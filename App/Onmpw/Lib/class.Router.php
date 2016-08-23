<?php
namespace Lib;

class Router{
    public static function router(){
        
        
        $urlarr = self::parseUrl();
        /*
         * �����ж�ģ������Ƿ����
         */
        if(in_array(\Common::C('URL:M_NAME'),array_keys($urlarr))){
            //������ڣ����жϵ�ǰ��ģ���Ƿ����
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
         * Ȼ���жϿ������Ƿ����
         */
        if (in_array(\Common::C('URL:A_NAME'), array_keys($urlarr)) && ! empty($urlarr[\Common::C('URL:A_NAME')])) {
            // ���������� ���Ҳ�Ϊ��
            $class = MODULE_NAME . '\\Action\\' . $urlarr[\Common::C('URL:A_NAME')] . 'Action';
            defined('AC_NAME') or define('AC_NAME',$urlarr[\Common::C('URL:A_NAME')]);
            if (class_exists($class)) {
                $class = new \ReflectionClass($class);
                /*
                 * ��ⷽ�������Ƿ����
                 */
                // ����ȡ���ο����������з��� ����ֻ���˳� public ����
                $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
                $methods = array_map(function ($val) { //���ûص����� ���� static ���������Ʒ��ظ�����
                    if(!$val->isStatic())
                        return $val->name;
                }, $methods);
                //ȥ����Ԫ��
                /* $methods = array_filter($methods); */
                $func = '';
                if (in_array(\Common::C('URL:F_NAME'), array_keys($urlarr)) && ! empty($urlarr[\Common::C('URL:F_NAME')])) {
                    // ���� ���Ҳ�Ϊ����ô��⵱ǰ���������Ƿ���ڴ˷���
                    $func = $urlarr[\Common::C('URL:F_NAME')]; // �� ���ʵķ�����ֵ������
                                                           // �����жϷ��������Ƿ���Ϲ淶
                    if (! preg_match('/^[A-Za-z](\w)*$/', $func)) // ���Ϻ��淶 �׳��쳣
                        throw new \ReflectionException();
                } elseif (empty($urlarr[\Common::C('URL:F_NAME')]) || ! in_array(\Common::C('URL:F_NAME'), array_keys($urlarr))) {
                    $func = 'index';
                }
                /*
                 * ���ж���Ϊ��ʹ�������Ʋ����ִ�Сд
                 * �жϵ�ǰ�����Ƿ��ڿ������д���
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
                 *���û���ҵ����� ���߸÷���Ϊ��̬static ���� ��ô�������  ����
                 */
                if (!$if || $class->getMethod($func)->isStatic()) {
                    echo 'Func not found!';
                } else {
                    defined('FC_NAME') or define('FC_NAME',$func);
                    $method = $class->getMethod($func);
                    //�õ����������ĸ���
                    $par = $method->getNumberOfParameters();
                    //�õ���������ĸ���
                    $rpar = $method->getNumberOfRequiredParameters();
                    $pararr = array(); //�����Ų������Ƶ�����
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
                            //���ݷ��صĲ�������ĸ���  �͸÷�������Ĳ����������Ƚ�
                            //���ǰ��С�ں��� ��ô ������������ ���� ����ú���
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
     * ����url
     * 
     * @return array
     * @access private
     */
    private static function parseUrl(){
        $uri = $_SERVER['QUERY_STRING'];
        $urlarr = array();
        if (! empty($uri)) {
            /*
             * ����uri
             */
            $url = parse_url($uri, PHP_URL_PATH);
            $url = explode('&', $url);
            // ����һ������ �����洢url��ģ�顢������������ ��������Ӧ��ֵ
//             $urlarr = array();
            /*
             * ����url���� ��ʼ����
            */
            foreach ($url as $val) {
                $a = explode('=', $val);
                //                 echo $a[0];echo "<br />";
                $urlarr[$a[0]] = $a[1];
                //                 print_r($urlarr);exit;
            }
        }else{
            if(isset($_SERVER['PATH_INFO']) && !empty(trim($_SERVER['PATH_INFO'],'/'))){
                //pathinfo ģʽ
                $uri = $_SERVER['PATH_INFO'];
                $uri = self::check($uri);
                $uri = explode('/', trim($uri,'/'));
                //ģ��
                if(($m = array_shift($uri)) != false){
                    $urlarr[\Common::C("URL:M_NAME")] = $m;
                }
                //������
                if(($a = array_shift($uri)) != false){
                    $urlarr[\Common::C("URL:A_NAME")] = $a;
                }
                //����
                if(($f = array_shift($uri)) != false){
                    $urlarr[\Common::C("URL:F_NAME")] = $f;
                }
                //����
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
         * ����Ƿ�����·�ɹ���
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
     * ����url��������
     * 
     * @param string $uri
     * @param int $parnum
     * @param array $parnames
     * @return mixed
     */
    private static function parseParam($uri = '',$parnum = 0,$parnames = array()){
        //��� GET �е� ģ�顢��������������������Ԫ��
        array_walk_recursive($_GET,'\\Common::Url_filter');
        //�����Ԫ��
        \Common::parse_empty($_GET);
        /*
         * ���û�в��������� ��ô����ʧ�� ����false
         */
        $str = '';
        if(empty($uri)) 
            if($parnum == 0)
                return false;
            else return array();
        
        /*
         * ���url������û���ҵ� / ˵��ֻ��һ������
         * Ȼ���жϲ��������Ƿ�Ϊ1
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
         * ȥ�� �����еĿյı���
         */
        \Common::parse_empty($uri);

        
        //�Ƚ�url�в����ĸ�����$parnum �Ĵ�С �������ߵ�����ȷ�������Ĳ���
        if(count($uri) <= 0){
            return false;   
        }elseif(count($uri) == 1){
            //���url�в����ĸ���Ϊ1 
            $_GET[$uri[0]] = '';
            if($parnum >= 0) return array($parnames[0] => $uri[0]);
            
            return false;
        }
        /*
         * �ж�url���ݵĲ������� �� �������������Ƚ�
         * ���ǰ�ߴ� ���պ���ѭ��
         * ���� ����ǰ��ѭ��
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