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
     * @throws ReflectionException
     * @throws RouterException
     */
    public function router()
    {
        $urlArr = $this->parseUrl();

        // 解析模块
        $this->parseModule($urlArr);

        // 然后判断控制器是否存在
        if (in_array(Common::C('URL:A_NAME'), array_keys($urlArr)) && !empty($urlArr[Common::C('URL:A_NAME')])) {
            // 控制器存在 并且不为空
            $class = MODULE_NAME . '\\Action\\' . ucwords(strtolower($urlArr[Common::C('URL:A_NAME')])) . 'Action';
            defined('AC_NAME') or define('AC_NAME', ucwords(strtolower($urlArr[Common::C('URL:A_NAME')])));
            if (class_exists($class)) {
                try {
                    $classReflection = new ReflectionClass($class);
                } catch (ReflectionException $e) {
                    throw new RouterException($e->getMessage());
                }

                // 检测方法参数是否存在
                // 首先取出该控制器的所有方法 并且只过滤出 public 方法
                $methods = $classReflection->getMethods(ReflectionMethod::IS_PUBLIC);
                $methods = array_map(function (ReflectionMethod $val) { //利用回调函数 将非 static 函数的名称返回给数组
                    if (!$val->isStatic()) {
                        return $val->name;
                    }
                }, $methods);

                //去除空元素
                $func = '';
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
                } elseif (empty($urlArr[Common::C('URL:F_NAME')]) || !in_array(Common::C('URL:F_NAME'), array_keys($urlArr))) {
                    $func = 'index';
                }

                // 此判断是为了使方法名称不区分大小写
                // 判断当前方法是否在控制器中存在
                $if = false;
                for ($i = 0; $i < count($methods); $i++) {
                    if (strtolower($func) == strtolower($methods[$i])) {
                        $func = $methods[$i];
                        $if = true;
                        break;
                    }
                }


                // 如果没有找到方法 或者该方法为静态static 方法 那么输出错误  否则
                if (!$if || $classReflection->getMethod($func)->isStatic()) {
                    throw new RouterException('Can not find Action!');
                } else {
                    defined('FC_NAME') or define('FC_NAME', $func);
                    $method = $classReflection->getMethod($func);

                    $args = $this->parseParam($urlArr[Common::C('URL:P_NAME')]??'', $method);


                    if (empty($args)) { // 说明没有参数
                        $method->invoke($classReflection->newInstance());
                    } else {
                        // 解析参数
                        $method->invokeArgs($classReflection->newInstance(), $args);
                    }

                }
            } else {
                throw new RouterException('Can not find Action!');
            }
        } else {
            throw new RouterException('Lack of Action');
        }

    }

    /**
     * 解析url
     *
     * @return array
     * @access private
     */
    private function parseUrl()
    {
        $uri = $this->request->getQueryString();

        $uri = $this->request->getRequestUri();

        // 定义一个数组 用来存储url的模块、控制器、方法 及其所对应的值
        if (!empty($uri)) {

            $urlArr = $this->getQueryStringUrlArr($uri);

        } else {

            $uri = $this->request->getRequestUri();

            $urlArr = $this->getRequestUriUrlArr($uri);

        }

        return $urlArr;
    }

    /**
     * 获取queryString 格式的请求数据
     *
     * @param $uri
     * @return array
     */
    private function getQueryStringUrlArr($uri): array
    {
        $urlArr = [];
        $urlPar = [];

        // 解析uri
        $url = explode('&',parse_url($uri, PHP_URL_PATH));

        // 遍历url数组 开始解析
        foreach ($url as $val) {
            $a = explode('=', $val);

            if (in_array($a[0], [Common::C("URL:M_NAME"), Common::C("URL:A_NAME"), Common::C("URL:F_NAME")])) {
                $urlArr[$a[0]] = $a[1];
                continue;
            }
            $urlPar = array_merge($urlPar, $a);
        }

        if (!empty($urlPar)) {
            $urlArr[Common::C("URL:P_NAME")] = implode('/', $urlPar);
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
            if (!empty($uri)) {
                $urlArr[Common::C("URL:P_NAME")] = implode('/', $uri);
            }
        } else {
            $urlArr[Common::C("URL:M_NAME")] = Common::C('DEFAULT_MODULE');
            $urlArr[Common::C("URL:A_NAME")] = Common::C('DEFAULT_ACTION');
            $urlArr[Common::C("URL:F_NAME")] = Common::C('DEFAULT_FUNC');
        }

        return $urlArr;
    }

    /**
     * 准备一条路由
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
            if (trim($rule, '/') == trim($uri, '/')) {
                return $route;
            }

            $matchRule = "/" . str_replace('/', '\/', $rule) . "/";
            if (strpos($rule, "/") === 0 && preg_match($matchRule, $uri, $matches)) {
                return $this->fetchPregRoute($route, $matches);
            }

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

        $uri = trim($uri,'/');
        if(empty($uri)){
            $uri = [];
        }else{
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
     *
     * @param $urlArr
     * @throws RouterException
     */
    private function parseModule($urlArr)
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
     *              例如： http://domain/p/12  则参数值为12
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
            $url = array_merge($url,[$matches[1][$i],$r[$i]]);
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
