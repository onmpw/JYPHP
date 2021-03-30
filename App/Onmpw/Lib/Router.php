<?php

namespace Lib;

use App;
use Exceptions\RouterException;
use ReflectionException;
use ReflectionClass;
use ReflectionMethod;
use Common;
use ReflectionParameter;

class Router
{

    private $request;

    private $app;

    private $urlParameters = [];

    /**
     * Router constructor.
     *
     * @param App $app
     * @param Request $request
     */
    public function __construct(App $app, Request $request)
    {
        $this->request = $request;

        $this->app = $app;
    }

    /**
     * 开始路由。
     *
     * @throws RouterException
     */
    public function router()
    {
        $urlArr = $this->parseUrl();

        $this->parseModule($urlArr);

        if (in_array(Common::C('URL:A_NAME'), array_keys($urlArr))
            && !empty($urlArr[Common::C('URL:A_NAME')])) {
            // 控制器存在 并且不为空 则开始解析控制器
            $class = MODULE_NAME . '\\Action\\' . ucwords(strtolower($urlArr[Common::C('URL:A_NAME')])) . 'Action';
            defined('AC_NAME') or define('AC_NAME', ucwords(strtolower($urlArr[Common::C('URL:A_NAME')])));
            $this->startAction($class,$urlArr);
        } else {
            throw new RouterException('Lack of Action');
        }

    }

    /**
     * 开始执行Action
     *
     * @param $action
     * @param $urlArr
     *
     * @throws RouterException
     */
    private function startAction($action,$urlArr)
    {
        if (class_exists($action)) {
            try {
                $actionReflection = new ReflectionClass($action);
            } catch (ReflectionException $e) {
                throw new RouterException($e->getMessage());
            }

            // 检测方法参数是否存在
            // 首先取出该控制器的所有方法 并且只过滤出 public 方法
            $methods = $actionReflection->getMethods(ReflectionMethod::IS_PUBLIC);
            $methods = array_filter($methods,function (ReflectionMethod $val) { //利用回调函数 将非 static 函数的名称返回给数组
                if ($val->isStatic()) {
                    return false;
                }
                return true;
            });

            $func = $this->getUrlMethodName($urlArr);

            // 此判断是为了使方法名称不区分大小写
            // 判断当前方法是否在控制器中存在
            $if = false;
            for ($i = 0; $i < count($methods); $i++) {
                if (strtolower($func) == strtolower($methods[$i]->name)) {
                    defined('FC_NAME') or define('FC_NAME', $methods[$i]->name);
                    $this->callMethod($methods[$i],$actionReflection,$urlArr);
                    $if = true;
                    break;
                }
            }

            if(!$if) {
                throw new RouterException("Can not find Method in $action Action!");
            }
        } else {
            throw new RouterException('Can not find Action!');
        }
    }

    /**
     * 开始调用方法
     *
     * @param ReflectionMethod $method
     * @param ReflectionClass $actionReflection
     * @param $urlArr
     */
    private function callMethod(ReflectionMethod $method,ReflectionCLass $actionReflection,$urlArr): void
    {
        $args = $this->parseParam($urlArr[Common::C('URL:P_NAME')] ?? '', $method);


        if (empty($args)) { // 说明没有参数
            $method->invoke($actionReflection->newInstance());
        } else {
            // 解析参数
            $method->invokeArgs($actionReflection->newInstance(), $args);
        }
    }



    /**
     * 获取url请求中指定的method
     *  如果url请求中的method 不是以字母开头的 则说明不符合规范，抛出异常
     *  如果没有指定method 或者指定的为空 则 访问控制器中的 index方法
     *
     * @param $urlArr
     * @return mixed|string
     * @throws RouterException
     */
    private function getUrlMethodName($urlArr)
    {
        if (in_array(Common::C('URL:F_NAME'), array_keys($urlArr)) && !empty($urlArr[Common::C('URL:F_NAME')])) {
            // 存在 并且不为空那么检测当前控制器中是否存在此方法
            $func = $urlArr[Common::C('URL:F_NAME')]; // 将 访问的方法赋值给变量
            // 首先判断方法名称是否符合规范
            if (!preg_match('/^[A-Za-z](\w)*$/', $func)) {
                // 不合乎规范 抛出异常
                $e = new RouterException("不合乎规范");
                $e->setRouter("router");
                throw $e;
            }
            return $func;
        }

        return 'index';
    }

    /**
     * 解析url
     *
     * @return array
     * @access private
     */
    private function parseUrl()
    {
        $uri = $this->request->getRequestUri();

        $queryString = $this->request->getQueryString();
        if (is_null($queryString)) {
            // 当 queryString 的值为null 说明url请求格式包含下面两种情况
            // 请求格式 1. http://domain/Module/Action/Method/P/V&p1=1&p2=2
            //         2. http://domain/&m=Module&a=Action&f=Method&p1=1&p2=2 / http://domain/&p1=1&p2=2
            // 这二种情况下认为参数是无效的，所以请求参数不做处理。只处理有效访问路径
            if (strpos($uri, '&') !== false) {
                $uri = substr($uri, 0, strpos($uri, '&'));
            }
        } else {
            $uri = trim(substr($uri, 0, strpos($uri, '?')), '/');

            // 当 queryString 的值不为null 说明url请求格式包含下面四种情况
            // 请求格式 1. http://domain/Module/Action/Method/P/V?p1=1&p2=2
            //         2. http://domain/?m=Module&a=Action&f=Method&p1=1&p2=2
            //         3. http://domain/?p1=1&p2=2
            //         4. http://domain/p/w/?p1=1&p2=2
            // 这1、2种情况下认为请求是有效的 第3种情况是没有指定模块，控制器和方法 所以无效
            // 第4中情况是开启了路由模式，相当于第一种情况
            if (empty($uri)) {
                // 2、3 种情况
                return $this->getQueryStringUrlArr($queryString);
            }
        }

        $urlArr = $this->getRequestUriUrlArr($uri);

        if (!is_null($queryString)) {
            $paramArr = $this->getQueryStringUrlArr($queryString, true);
            if (isset($urlArr[Common::C('URL:P_NAME')]) && isset($paramArr[Common::C('URL:P_NAME')])) {
                $urlArr[Common::C('URL:P_NAME')] = $urlArr[Common::C('URL:P_NAME')] . '/' . $paramArr[Common::C('URL:P_NAME')];
            }
        }

        return $urlArr;
    }

    /**
     * 获取queryString 格式的请求数据
     *
     * @param $uri
     * @param bool $isParam
     * @return array
     */
    private function getQueryStringUrlArr($uri, $isParam = false): array
    {
        $urlArr = [];
        $urlPar = [];

        // 解析uri
        $url = explode('&', parse_url($uri, PHP_URL_PATH));

        // 遍历url数组 开始解析
        foreach ($url as $val) {
            $a = explode('=', $val);

            if (in_array($a[0], [Common::C("URL:M_NAME"), Common::C("URL:A_NAME"), Common::C("URL:F_NAME")]) && !$isParam) {
                $urlArr[$a[0]] = $a[1];
                continue;
            }
            $urlPar = array_merge($urlPar, $a);
        }

        if (!empty($urlPar)) {
            $this->setParam($urlArr, $urlPar);
        }

        return $urlArr;
    }

    /**
     * 获取RequestUri（pathInfo） 格式的请求数据
     *
     * @param $uri
     * @return array
     */
    private function getRequestUriUrlArr($uri): array
    {
        $urlArr = [];
        if (!empty($uri) && $uri != '/') {
            //pathInfo 模式
            $uri = explode('/', trim($this->prepare($uri), '/'));
            //模块
            if (($m = array_shift($uri)) != false) {
                $urlArr[Common::C("URL:M_NAME")] = $m;
            }
            //控制器
            if (($a = array_shift($uri)) != false) {
                $urlArr[Common::C("URL:A_NAME")] = $a;
            }
            //方法
            if (($f = array_shift($uri)) != false) {
                $urlArr[Common::C("URL:F_NAME")] = $f;
            }

            //参数
            $this->setParam($urlArr, $uri);

        } else {
            $urlArr[Common::C("URL:M_NAME")] = Common::C('DEFAULT_MODULE');
            $urlArr[Common::C("URL:A_NAME")] = Common::C('DEFAULT_ACTION');
            $urlArr[Common::C("URL:F_NAME")] = Common::C('DEFAULT_FUNC');
        }

        return $urlArr;
    }

    /**
     * 解析并设置请求的参数
     *
     * @param $urlArr
     * @param $uriParam
     */
    private function setParam(&$urlArr, $uriParam): void
    {
        if (!empty($uriParam)) {
            if (count($uriParam) % 2 != 0) {
                // 参数个数为奇数
                array_splice($uriParam, 0, count($uriParam) - 1);
            }
            $urlArr[Common::C("URL:P_NAME")] = implode('/', $uriParam);
        }
    }

    /**
     * 准备一条路由
     *  其中第二种和第三种是不能混合在一起的，例如下面的路由就是非法的
     *  /p/(\w+)/:id/:name => /Admin/Index/:1   目前系统无法解析
     *
     * @param $uri
     * @return string|string[]
     */
    private function prepare($uri)
    {

        // 检测是否开启了路由功能
        if (!Common::C("ROUTER:START")) {
            return $uri;
        }

        $routes = Common::C("ROUTER:RULE");
        $route = '';
        foreach ($routes as $rule => $route) {

            // 第一种 全等的路由规则
            // 例如： /p/c => /Admin/Index/index   请求的url 为 http://domain/p/c  则实际访问的是 http://domain/Admin/Index/index
            //      url后面是可以跟上参数的 例如 http://domain/p/c?test=1&test2=2  则也是合法的url地址
            //
            if (trim($rule, '/') == trim($uri, '/')) {
                return $route;
            }

            // 第二种 简单的带正则表达式的路由
            // 例如： /p/(\w+)=>/Admin/Index/:1  指定Admin模块的Action为Index的中的任意的合法的方法
            $matchRule = "/" . str_replace('/', '\/', $rule) . "/";
            if (strpos($rule, "/") === 0 && preg_match($matchRule, $uri, $matches)) {
                return $this->fetchPregRoute($route, $matches);
            }

            // 第三种 不带正则表达式但是可以指定参数的路由
            // 例如： /p/:id/:name=>/Admin/Index/index  指定了具体的module(Admin)、控制器(Index)和方法(index)
            //       并且参数为 $id=? 和 $name=?  当然也可以通过 $request->get() 方法来获取参数
            //       如 function index($id,$name){}  或者 function index(Request $request){$id=$request->get('id');}
            if (preg_match_all('/(?:[\w\d]+\/)?:([\w\d]+)/i', $rule, $matches)) {
                if ($route = $this->fetchParameterRoute($rule, $route, $uri, $matches)) {
                    break;
                }
            }
        }

        return $route;
    }

    /**
     * 解析url参数方法
     *
     * @param string $uri
     * @param ReflectionMethod $method
     *
     * @return mixed
     */
    private function parseParam($uri, ReflectionMethod $method)
    {
        //清除 GET 中的 模块、控制器、方法、参数等元素
        array_walk_recursive($_GET, 'Common::UrlFilter');
        //清除空元素
        Common::parseEmpty($_GET);

        $uri = trim($uri, '/');
        if (empty($uri)) {
            $uri = [];
        } else {
            $uri = explode('/', trim($uri, '/'));
        }

        $this->urlParameters = $this->getUriParameter($uri);

        // uri中的参数不为空，并且方法参数也不为空
        // 下面开始解析参数
        $parameterInfo = [];
        array_map(function ($val) use (&$uri, &$parameterInfo) {
            $this->setMethodParameter($val, $parameterInfo, $uri);
        }, $method->getParameters());

        return $parameterInfo;
    }

    /**
     * 解析url地址中的参数
     *
     * @param array $uri
     *
     * @return array
     */
    private function getUriParameter(array $uri)
    {
        $parameterInfo = [];
        while (!empty($uri)) {
            // 当uri的个数为1 时，说明元素个数时奇数，则退出循环
            if (count($uri) == 1) {
                break;
            }

            $parameterInfo[array_shift($uri)] = array_shift($uri);
        }

        return $parameterInfo;
    }

    /**
     * 根据action的方法中的参数名称，从url中的参数获取值
     *
     * @param $name
     * @param $uri
     *
     * @return mixed|null
     */
    private function getMethodParameterFromUriParameter($name, $uri)
    {
        if (count($uri) == 0) {
            return null;
        }

        if (count($uri) == 1) {
            return $uri[0];
        }

        if (empty($this->urlParameters)) {
            $this->urlParameters = $this->getUriParameter($uri);
        }

        return $this->urlParameters[$name] ?? null;
    }

    /**
     * 设置action方法的参数
     *
     * @param ReflectionParameter $parameter
     * @param $parameterInfo
     * @param $uri
     *
     * @throws ReflectionException
     * @throws RouterException
     */
    private function setMethodParameter(ReflectionParameter $parameter, &$parameterInfo, &$uri): void
    {
        $class = $parameter->getClass();

        if (is_null($class)) {
            $parVal = $this->getMethodParameterFromUriParameter($parameter->name, $uri);
            if (!is_null($parVal)) {
                $parameterInfo[$parameter->name] = $this->convertValType($parameter, $parVal);
            } else {
                // 请求的参数已经用完，查看方法中的参数是否有默认值
                if ($parameter->isDefaultValueAvailable()) {
                    // 参数本身带有默认值
                    $parameterInfo[$parameter->name] = $parameter->getDefaultValue();
                } else {
                    // 参数没有默认值，并且也没有在url参数中找到，则抛出异常
                    throw new RouterException("Parameter \${$parameter->name} Is Required!");
                }
            }
        } else {
            if ($class->name == Request::class) {
                $parameterInfo[$parameter->name] = $this->request->createFromNewGlobal($this->urlParameters);
            } else {
                $parameterInfo[$parameter->name] = $this->app->make($class->name);
            }
        }
    }

    /**
     * 根据方法中参数的类型转换url请求中的参数值
     *
     * @param ReflectionParameter $parameter
     * @param $val
     * @return int|string
     */
    private function convertValType(ReflectionParameter $parameter, $val)
    {
        $type = $parameter->getType();
        if (is_null($type)) {
            return $val;
        }

        switch ($type->getName()) {
            case "int":
                return intval($val);
            default:
                return (string)$val;
        }
    }

    /**
     * 解析模块
     * 查看url中指定的module是否存在或者是否在url中指定了module参数
     * 如果module不存在或者url中没有指定module参数 则程序抛出异常
     *
     * @param $urlArr
     * @throws RouterException
     */
    private function parseModule($urlArr): void
    {
        if (in_array(Common::C('URL:M_NAME'), array_keys($urlArr))) {
            //如果存在，则判断当前的模块是否存在
            if (in_array(strtolower($urlArr[Common::C('URL:M_NAME')]), array_map(function ($v) {
                return strtolower($v);
            }, Common::C('MODULE')))) {
                defined('MODULE_NAME') or define('MODULE_NAME', ucwords(strtolower($urlArr[Common::C('URL:M_NAME')])));
            } elseif (!empty($urlArr[Common::C('URL:M_NAME')])) {
                $e = new RouterException("模块不存在");
                $e->setRouter($urlArr);
                throw $e;
            } else {
                defined('MODULE_NAME') or define('MODULE_NAME', Common::C('DEFAULT_MODULE'));
            }
        } else {
            $e = new RouterException("指定的模块参数名称错误");
            $e->setRouter($urlArr);
            throw $e;
        }
    }

    /**
     * 获取实际路由
     * 对于pathinfo模式的路由，遵循以下规则
     *      1. /p/index => /Web/Index/index  // 表示访问的是 Web模块下的Index控制下的index方法  例如：http://domain/p/index;
     *      2. /p/(\w+) => /Web/Index/:1     // 表示访问的是 Web模块下的Index控制器下的任意有效的方法
     *          例如：     http://domain/p/login  则访问的是 Web模块下的Index控制器中的login方法；
     *                    http://domain/p/index  则访问的是 Web模块下的Index控制器中的index方法。
     *      其实相当于 p 是 Web/Index的简化形式
     *      也可以通过正则表达式放置参数
     *      3. /p/(\w+)/(\d+)=> /Web/Index/:1/:2  其中 :2 就是参数  可以通过 \Common:get('p') 访问参数
     *
     * 该种方式不带参数，也就是说控制器中不会接收到参数
     *
     * @param $route
     * @param array $matches
     * @return string|string[]
     */
    private function fetchPregRoute($route, array $matches)
    {
        array_shift($matches);
        $match = array();
        for ($i = 0; $i < count($matches); $i++) {
            $match[':' . ($i + 1)] = $matches[$i];
        }
        foreach ($match as $key => $val) {
            $route = str_replace($key, $val, $route);
        }

        return $route;
    }

    /**
     * 获取带有参数的路由
     * 对于pathInfo模式的路由，遵循以下规则
     *      1. /p/:id => /Web/Index/index  //表示访问的是 Web模块下的Index控制器中的index方法，参数在访问请求中指定
     *              例如： http://domain/p/12  则参数值为12 参数名称为id
     *
     * @param $rule
     * @param $route
     * @param $uri
     * @param $matches
     * @return bool|string|string[]
     */
    private function fetchParameterRoute($rule, $route, $uri, $matches)
    {
        $url = explode('/', trim($uri, '/'));
        $r = explode('/', trim(substr($rule, 0, strpos($rule, ':') - 1), '/'));

        if (implode('/', $r) != implode('/', array_slice($url, 0, count($r)))) {
            return false;
        }

        $r = array_slice($url, count($r));
        if (count($matches[1]) != count($r)) {
            return false;
        }

        $url = array();
        for ($i = 0; $i < count($r); $i++) {
            $url = array_merge($url, [$matches[1][$i], $r[$i]]);
        }
        foreach ($url as $key => $val) {
            $route = str_replace($key, $val, $route, $count);
            if ($count == 1) {
                unset($url[$key]);
            }
        }
        if (count($url) > 0) {
            $route = rtrim($route, '/') . '/' . implode('/', $url);
        }
        return $route;
    }
}
