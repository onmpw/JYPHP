<?php
namespace Lib;

class Router{
    public static function router(){
        
        
        $urlarr = self::parseUrl();
        /*
         * 首先判断模块参数是否存在
         */
        if(in_array(\Common::C('URL:M_NAME'),array_keys($urlarr))){
            //如果存在，则判断当前的模块是否存在
            if(in_array($urlarr[\Common::C('URL:M_NAME')],\Common::C('MODULE'))){
                defined('MODULE_NAME') or define('MODULE_NAME',$urlarr[\Common::C('URL:M_NAME')]);
            }elseif(!empty($urlarr[\Common::C('URL:M_NAME')])){
                echo "Error!";
                exit;
            }else{
                defined('MODULE_NAME') or define('MODULE_NAME',\Common::C('DEFAULT_MODULE'));
            }
        }else{
            defined('MODULE_NAME') or define('MODULE_NAME',\Common::C('DEFAULT_MODULE'));
        }
        
        /*
         * 然后判断控制器是否存在
         */
        if (in_array(\Common::C('URL:A_NAME'), array_keys($urlarr)) && ! empty($urlarr[\Common::C('URL:A_NAME')])) {
            // 控制器存在 并且不为空
            $class = MODULE_NAME . '\\Action\\' . $urlarr[\Common::C('URL:A_NAME')] . 'Action';
            defined('AC_NAME') or define('AC_NAME',$urlarr[\Common::C('URL:A_NAME')]);
            if (class_exists($class)) {
                $class = new \ReflectionClass($class);
                /*
                 * 检测方法参数是否存在
                 */
                // 首先取出次控制器的所有方法 并且只过滤出 public 方法
                $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
                $methods = array_map(function ($val) { //利用回调函数 将非 static 函数的名称返回给数组
                    if(!$val->isStatic())
                        return $val->name;
                }, $methods);
                //去除空元素
                /* $methods = array_filter($methods); */
                $func = '';
                if (in_array(\Common::C('URL:F_NAME'), array_keys($urlarr)) && ! empty($urlarr[\Common::C('URL:F_NAME')])) {
                    // 存在 并且不为空那么检测当前控制器中是否存在此方法
                    $func = $urlarr[\Common::C('URL:F_NAME')]; // 将 访问的方法赋值给变量
                                                           // 首先判断方法名称是否符合规范
                    if (! preg_match('/^[A-Za-z](\w)*$/', $func)) // 不合乎规范 抛出异常
                        throw new \ReflectionException();
                } elseif (empty($urlarr[\Common::C('URL:F_NAME')]) || ! in_array(\Common::C('URL:F_NAME'), array_keys($urlarr))) {
                    $func = 'index';
                }
                /*
                 * 此判断是为了使方法名称不区分大小写
                 * 判断当前方法是否在控制器中存在
                 */
                $if = false;
                for ($i = 0; $i < count($methods); $i++) {
                    if (strtolower($func) == strtolower($methods[$i])) {
                        $func = $methods[$i];
                        $if = true;
                        break;
                    }
                }
                
                /*
                 *如果没有找到方法 或者该方法为静态static 方法 那么输出错误  否则
                 */
                if (!$if || $class->getMethod($func)->isStatic()) {
                    echo 'Func not found!';
                } else {
                    defined('FC_NAME') or define('FC_NAME',$func);
                    $method = $class->getMethod($func);
                    //得到方法参数的个数
                    $par = $method->getNumberOfParameters();
                    //得到必须参数的个数
                    $rpar = $method->getNumberOfRequiredParameters();
                    $pararr = array(); //定义存放参数名称的数组
                    if($par > 0){
                        $pararr = array_map(function($val){return $val->name;},$method->getParameters());
                    }
//                     $args = in_array(\Common::C('URL:P_NAME'),array_keys($urlarr)) ? self::parseParam($urlarr[\Common::C('URL:P_NAME')],$par,$pararr) : false;
                    if(in_array(\Common::C('URL:P_NAME'),array_keys($urlarr))){
                        $args = self::parseParam($urlarr[\Common::C('URL:P_NAME')],$par,$pararr);
                    }else{
                        $args = array();
                    }
                    try {
                        if(false === $args){
                            $method->invoke($class->newInstance());
                        }elseif(is_array($args)){
                            //根据返回的参数数组的个数  和该方法必须的参数个数做比较
                            //如果前者小于后者 那么 参数个数错误 否则 则调用函数
                            if(count($args) < $rpar){
                                echo "Paramater Is Required!";
                            }else{
                                $method->invokeArgs($class->newInstance(), $args);
                            }
                        }
                    } catch (\ReflectionException $e) {
                        echo $e->getMessage();
                    }
                }
            } else {
                echo 'Can not found Action!';
            }
        }else{
            echo 'Lack of Action';
        }
        
    }
    /**
     * 解析url
     * 
     * @return array
     * @access private
     */
    private static function parseUrl(){
        $uri = $_SERVER['QUERY_STRING'];
        $urlarr = array();
        if (! empty($uri)) {
            /*
             * 解析uri
             */
            $url = parse_url($uri, PHP_URL_PATH);
            $url = explode('&', $url);
            // 定义一个数组 用来存储url的模块、控制器、方法 及其所对应的值
//             $urlarr = array();
            /*
             * 遍历url数组 开始解析
            */
            foreach ($url as $val) {
                $a = explode('=', $val);
                //                 echo $a[0];echo "<br />";
                $urlarr[$a[0]] = $a[1];
                //                 print_r($urlarr);exit;
            }
        }else{
            if(isset($_SERVER['PATH_INFO']) && !empty(trim($_SERVER['PATH_INFO'],'/'))){
                //pathinfo 模式
                $uri = $_SERVER['PATH_INFO'];
                $uri = self::check($uri);
                $uri = explode('/', trim($uri,'/'));
                //模块
                if(($m = array_shift($uri)) != false){
                    $urlarr[\Common::C("URL:M_NAME")] = $m;
                }
                //控制器
                if(($a = array_shift($uri)) != false){
                    $urlarr[\Common::C("URL:A_NAME")] = $a;
                }
                //方法
                if(($f = array_shift($uri)) != false){
                    $urlarr[\Common::C("URL:F_NAME")] = $f;
                }
                //参数
                if(!empty($uri)){
                    $urlarr[\Common::C("URL:P_NAME")] = implode('/', $uri);
                }
                
            }else{
                $urlarr[\Common::C("URL:M_NAME")] = \Common::C('DEFAULT_MODULE');
                $urlarr[\Common::C("URL:A_NAME")] = \Common::C('DEFAULT_ACTION');
                $urlarr[\Common::C("URL:F_NAME")] = \Common::C('DEFAULT_FUNC');
            }
        }
        return $urlarr;
    }
    
    private static function check($uri){
        /*
         * 检测是否开启了路由功能
         */
        if(!\Common::C("ROUTER:START")){
            return $uri;
        }
        $routes = \Common::C("ROUTER:RULE");
        foreach($routes as $rule=>$route){
            if(strpos($rule, "/")===0 && preg_match($rule, $uri,$matches)){
                array_shift($matches);
                $match = array();
                for($i=0;$i<count($matches);$i++){
                    $match[':'.($i+1)] = $matches[$i];
                }
                foreach($match as $key=>$val){
                    $route = str_replace($key, $val, $route);
                }
               return $route;
            }
            if(trim($rule,'/') == trim($uri,'/')) return $route;
            $url = explode('/',trim($uri,'/'));
            if(preg_match_all('/(?:[\w\d]+\/)?:([\w\d]+)/i',$rule,$matches)){
                $r = explode('/',substr($rule,0,strpos($rule, ':')-1));
                if(implode('/',$r) != implode('/',array_slice($url,0,count($r)))){
                    continue;
                }
                $r = array_slice($url,count($r));
                if(count($matches[1]) != count($r)) continue;
                $url = array();
                for($i = 0;$i<count($r);$i++){
                    $url[':'.$matches[1][$i]] = $r[$i];
                }
                foreach($url as $key=>$val){
                    $route = str_replace($key, $val, $route,$count);
                    if($count == 1) unset($url[$key]);
                }
                if(count($url)>0) $route = rtrim($route,'/').'/'.implode('/',array_values($url));
                return $route;
            }
        }
        return $uri;
    }
    /**
     * 解析url参数方法
     * 
     * @param string $uri
     * @param int $parnum
     * @param array $parnames
     * @return mixed
     */
    private static function parseParam($uri = '',$parnum = 0,$parnames = array()){
        //清除 GET 中的 模块、控制器、方法、参数等元素
        array_walk_recursive($_GET,'\\Common::Url_filter');
        //清除空元素
        \Common::parse_empty($_GET);
        /*
         * 如果没有参数传进来 那么解析失败 返回false
         */
        $str = '';
        if(empty($uri)) 
            if($parnum == 0)
                return false;
            else return array();
        
        /*
         * 如果url参数中没有找到 / 说明只有一条数据
         * 然后判断参数个数是否为1
         */
        if(!strpos($uri,'/')){
            $_GET[$uri] = '';
            if($parnum >=1)
                return array($parnames[0]=>$uri);
            else return false;
        }
        $par = array();
        $uri = explode('/',$uri);
        
        /*
         * 去除 数组中的空的变量
         */
        \Common::parse_empty($uri);

        
        //比较url中参数的个数和$parnum 的大小 根据两者的数量确定方法的参数
        if(count($uri) <= 0){
            return false;   
        }elseif(count($uri) == 1){
            //如果url中参数的个数为1 
            $_GET[$uri[0]] = '';
            if($parnum >= 0) return array($parnames[0] => $uri[0]);
            
            return false;
        }
        /*
         * 判断url传递的参数个数 和 方法参数个数比较
         * 如果前者大 则按照后者循环
         * 否则 按照前者循环
         */
        if(count($uri) <= $parnum){
            for($i = 0; $i<count($uri); $i++)
                $par[$parnames[$i]] = $uri[$i];
        }else{
            for($i = 0; $i<$parnum; $i++)
                $par[$parnames[$i]] = $uri[$i];
        }
        while(count($uri) > 0){
            $k = $uri[0];
            array_shift($uri);
            $v = $uri[0];
            array_shift($uri);
            $_GET[$k] = $v;
        }
        if(count($par) > 0) return $par;
        return false;
    }
}