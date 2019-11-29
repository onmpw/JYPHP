<?php

/**
 * 
 * @author 刘汉增
 *
 */
class Common{
   
    /*
     * 类库的缓存
     */
    private static $_class = array();

    private static $_files = array();
    
    /*
     * 配置文件中的缓存
     */
    private static $_conf = array();
    
    /*
     * 第三方类库的缓存
     */
    private static $_ext = array();
    
    const EXT = 'php';
    
    /**
     * 导入类库函数
     * 如果开头是 @|# 表示后面跟的是带命名空间的类
     * # 代表导入初始化类 
     * 
     * @param string $name
     * 
     * @param string $dir
     * 
     * @param string $ext
     * 
     * @return boolean
     */
    public static function Import($name,$dir = '',$ext = '.php'){
        $name = str_replace('\\', '/',$name);
        /*
         * 首先检测要引入的类之前是否引入过
         */
        if(isset(self::$_class[$name.'-'.$dir]))
            return true;
        else
            self::$_class[$name.'-'.$dir] = true;
        
        /*
         * 分析引入的文件属于哪一种文件
         */
        $class_struct = explode('/',$name);
        
        $ft = '';  //初始化定义文件类型变量
        
        if(empty($dir)){
            if('Lib' == $class_struct[0]){
                $dir = LIB_PATH;
                $ft = 'class.';
            }elseif('Inter' == $class_struct[0]){
                $dir = INTERFACE_PATH;
                $ft = 'interface.';
            }elseif('Ext' == $class_struct[0]){
                $dir = EXT_PATH;
                $ft = 'class.';
            }elseif('@' == $class_struct[0]){
                $dir = MODULE_PATH;
                $ft = 'Action.';
                $name = substr_replace($name, '', 0 ,strlen($class_struct[0])+1);
            }elseif('#' == $class_struct[0]){
                $dir = APP_PATH;
                $ft = 'class.';
                $name = substr_replace($name, '', 0 ,strlen($class_struct[0])+1);
            }
        }
        $name = substr($name,0,-strlen($class_struct[count($class_struct)-1])).$ft.$class_struct[count($class_struct)-1];
        if(substr($dir,-1) != '/')
            $dir .= '/';
        return self::Require_file($dir.$name.$ext);
    }
    
    /**
     * 导入第三方类库
     * 如果$ext中带有 @ 那么@后面的表示文件名的后缀  在扩展名之前，在文件名之后
     * 如果$ext中带有 # 那么#后面的表示文件名的前缀  在文件名之前
     * 
     * @param string $ext
     * $ext 如果带有 : 那么 : 代表的是路径  最后一个: 之后的是文件名
     * 
     * @return boolean
     */
    public static function Ext($ext = ''){
        if(empty($ext)) return false;
        if(isset(self::$_ext[$ext])){
            return true; 
        }
        
        /*
         * 利用正则表达式匹配当前的类信息
         * 两种情况 一种是    字符串@|#文件类型
         * 一种是    字符串
         * 
         * 其它情况返回false
         */
        if(preg_match('/^([\w:]+)[@#]{1}([\w]+)$/', $ext,$matches)){
            $fext = $matches[2].'.';
            $finfo = $matches[1];
        }elseif(preg_match('/^([\w:]+)$/',$ext,$matches)){
            $fext = '';
            $finfo = $matches[1];
        }else{
            return false;
        }
        
        //查找是否存在:
        // : 表示 / 也就是分割路径
        if(strpos($finfo,':')){
            $fname = ltrim(strrchr($finfo,':'),':'); //得到文件名称 最后一个 : 之后的字符串
            $finfo = substr($finfo,0,strrpos($finfo,':'));  //得到最后一个: 之前的字符串 这是文件所在路径信息
        }else{
            $fname = $finfo;
        }
        //将 : 替换成 /
        $finfo = str_replace(':','/',$finfo).'/';
        
        if(strpos($ext,'@'))
            $fname = $fname.'.'.$fext.self::EXT;  // 如果是 @ 那么 文件扩展信息 在文件名之后 扩展名之前
        elseif(strpos($ext,'#')) 
            $fname = $fext.$fname.".".self::EXT; //如果是# 那么文件扩展信息在文件名之前
        else
            $fname = $fname.'.'.self::EXT;  //最后一种情况是 没有扩展信息
        return self::Require_file(EXT_PATH.$finfo.$fname);
    }
    
    /**
     * 引入文件函数
     * 
     * @param string $file
     * 
     * @return boolean
     */
    public static function Require_file($file){
        if(isset(self::$_files[$file])){
            return true;
        }else{
            if (file_exists($file)) {
                require $file;
                self::$_files[$file] = true;
            }else{
                self::$_files[$file] = false;
            }
        }
        return self::$_files[$file];
    }

    /**
     * 带缓存的载入配置函数
     * 首先判断如果是数组的话，则将其加入$_conf 中
     * 如果是字符串，说明是取配置值 如果存在 则直接返回，如果缓存中不存在 那么在配置文件中查找
     * 二维关联数组查找方式
     * C(name:name-key)
     * @param $conf
     * @return mixed
     */
    public static function C($conf){
        /*
         * 首先判断参数的类型
         */
        if(is_array($conf)){
            //参数类型是数组 加入缓存数组
            foreach($conf as $key=>$val){
                self::$_conf[$key] = $val;
            }
            return '';
        }
        if(is_string($conf)){
            //参数类型是字符串 开始进行查找操作
            if(strpos($conf,':')){
                //查找二维关联数组的配置
                $conf = explode(':', $conf);
                if (count($conf) == 2) {
                    /*
                     * 如果 解析后的数组大小是2 说明是二维关联数组
                     * 如果缓存中存在 直接返回
                     */
                    if (isset(self::$_conf[$conf[0]]) && is_array(self::$_conf[$conf[0]])) {
                        if (isset(self::$_conf[$conf[0]][$conf[1]]))
                            return self::$_conf[$conf[0]][$conf[1]];
                    }
                    /*
                     * 缓存数组中不存在 那么首先检查当前模块的配置文件中是否存在
                     */
                    if(file_exists(MODULE_PATH.MODULE_NAME.'/Config/app.php')){
                        $C = require(MODULE_PATH.MODULE_NAME.'/Config/app.php');
                        if(isset($C[$conf[0]][$conf[1]])){
                            self::$_conf[$conf[0]][$conf[1]] = $C[$conf[0]][$conf[1]];
                            return $C[$conf[0]][$conf[1]];
                        }
                    }
                    /*
                     * 如果当前数组中不存在 则查找公共配置文件中是否存在
                     */
                    $C = require(CONFIG_PATH . 'app.php');
                    if(isset($C[$conf[0]][$conf[1]])){
                        self::$_conf[$conf[0]][$conf[1]] = $C[$conf[0]][$conf[1]];
                        return $C[$conf[0]][$conf[1]];
                    };
                    return '';
                }
                return '';
            }
            
            if(isset(self::$_conf[$conf])) return self::$_conf[$conf];
            /*
             * 缓存数组中不存在 那么首先检查当前模块的配置文件中是否存在
             */
            if(file_exists(MODULE_PATH.MODULE_NAME.'/Config/app.php')){
                $C = require(MODULE_PATH.MODULE_NAME.'/Config/app.php');
                if(isset($C[$conf])){
                    self::$_conf[$conf] = $C[$conf];
                    return $C[$conf];
                };
            }
            /*
             * 如果当前数组中不存在 则查找公共配置文件中是否存在
             */
            $C = require(CONFIG_PATH . 'app.php');
            if(isset($C[$conf])){
                self::$_conf[$conf] = $C[$conf];
                return $C[$conf];
            }
            return '';
        }
        return '';
    }
    
    /**
     * 加载配置文件函数
     * 
     * @param string $conf_file
     * 
     * @return mixed
     */
    public static function Load_conf($conf_file = ''){
        if(empty($conf_file)){
            return require_once CONFIG_PATH . 'app.php';
        }
        if(file_exists($conf_file)){
            return require_once $conf_file;
        }
        return false;
    }
    
    /**
     * 过滤url中的 模块、控制器、方法、参数等数据
     * 
     * @param string $val
     * @param string $key
     */
    public static function Url_filter(&$val,$key){
        if(in_array($key,array_values(self::C('URL'))))
            $val = '';
    }
    /**
     * 删除数组中值为空的变量
     * @param array $arr
     */
    public static function parse_empty(array &$arr){
        foreach($arr as $k=>$v){
            if(empty($v)){
                unset($arr[$k]);
            }
        }
    }
    
    /**
     * 过滤字符串函数
     * @access public static
     * @param string $val
     * @return string
     */
    public static function escapeString($val){
        return addslashes($val);
    }

    /**
     * 过滤smarty 常量的格式
     *
     * @param $tplName
     * @return string|string[]|null
     */
    public static function smarty_constant_filter($tplName){
        $str = preg_replace('/__([a-zA-Z]*)__/', '{$smarty.const.\\1}', $tplName);
        return $str;
    }
    
    /**
     * 重写 $_GET
     * @param string $key
     * @return boolean|string<>
     */
    public static function get($key = ''){
        static $data = array();
        if(empty($key)) return false; //如果$key 为空 则返回 false
        if(isset($data[$key])) return $data[$key];  //如果 当前参数不正确 返回空
        if(isset($_GET)){
            $data = $_GET;
            unset($_GET);
        }
        foreach($data as $k=>$v){
            $data[$k] = addslashes(trim($v));
        }
        if(!isset($data[$key])) return false;
        return $data[$key];
    }
    
    /**
     * 重写 $_POST
     * @param string $key
     * @return boolean|string <>
     */
    public static function post($key = ''){
        static $data = array();
        if(empty($key)) return false;
        if(isset($data[$key])) return $data[$key];
        if(isset($_POST)){
            $data = $_POST;
            unset($_POST);
        }
        foreach($data as $k=>$v){
            $data[$k] = htmlspecialchars(addslashes($v));
        }
        if(!isset($data[$key])) return false;
        return $data[$key];
    }
    
    /**
     * session管理函数
     * @param string|array $name session名称 如果为数组则表示进行session设置
     * @param mixed $value session值
     * @return mixed
     */
    public static function session($name='',$value='') {
        $prefix   =  self::C('SESSION_PREFIX');
        if(is_array($name)) { // session初始化 在session_start 之前调用
            if(isset($name['prefix'])) self::C(['SESSION_PREFIX',$name['prefix']]);
            if(self::C('VAR_SESSION_ID') && isset($_REQUEST[self::C('VAR_SESSION_ID')])){
                session_id($_REQUEST[self::C('VAR_SESSION_ID')]);
            }elseif(isset($name['id'])) {
                session_id($name['id']);
            }
            if('common' == APP_MODE){ // 其它模式可能不支持
                ini_set('session.auto_start', 0);
            }
            if(isset($name['name']))            session_name($name['name']);
            if(isset($name['path']))            session_save_path($name['path']);
            if(isset($name['domain']))          ini_set('session.cookie_domain', $name['domain']);
            if(isset($name['expire']))          {
                ini_set('session.gc_maxlifetime',   $name['expire']);
                ini_set('session.cookie_lifetime',  $name['expire']);
            }
            if(isset($name['use_trans_sid']))   ini_set('session.use_trans_sid', $name['use_trans_sid']?1:0);
            if(isset($name['use_cookies']))     ini_set('session.use_cookies', $name['use_cookies']?1:0);
            if(isset($name['cache_limiter']))   session_cache_limiter($name['cache_limiter']);
            if(isset($name['cache_expire']))    session_cache_expire($name['cache_expire']);
            if(isset($name['type']))            self::C(['SESSION_TYPE',$name['type']]);
            if(self::C('SESSION_TYPE')) { // 读取session驱动
                $type   =   self::C('SESSION_TYPE');
                $class  =   strpos($type,'\\')? $type : 'Think\\Session\\Driver\\'. ucwords(strtolower($type));
                $hander =   new $class();
                session_set_save_handler(
                    array(&$hander,"open"),
                    array(&$hander,"close"),
                    array(&$hander,"read"),
                    array(&$hander,"write"),
                    array(&$hander,"destroy"),
                    array(&$hander,"gc"));
            }
            // 启动session
            if(self::C('SESSION_AUTO_START'))  session_start();
        }elseif('' === $value){
            if(''===$name){
                // 获取全部的session
                return $prefix ? $_SESSION[$prefix] : $_SESSION;
            }elseif(0===strpos($name,'[')) { // session 操作
                if('[pause]'==$name){ // 暂停session
                    session_write_close();
                }elseif('[start]'==$name){ // 启动session
                    session_start();
                }elseif('[destroy]'==$name){ // 销毁session
                    $_SESSION =  array();
                    session_unset();
                    session_destroy();
                }elseif('[regenerate]'==$name){ // 重新生成id
                    session_regenerate_id();
                }
            }elseif(0===strpos($name,'?')){ // 检查session
                $name   =  substr($name,1);
                if(strpos($name,'.')){ // 支持数组
                    list($name1,$name2) =   explode('.',$name);
                    return $prefix?isset($_SESSION[$prefix][$name1][$name2]):isset($_SESSION[$name1][$name2]);
                }else{
                    return $prefix?isset($_SESSION[$prefix][$name]):isset($_SESSION[$name]);
                }
            }elseif(is_null($name)){ // 清空session
                if($prefix) {
                    unset($_SESSION[$prefix]);
                }else{
                    $_SESSION = array();
                }
            }elseif($prefix){ // 获取session
                if(strpos($name,'.')){
                    list($name1,$name2) =   explode('.',$name);
                    return isset($_SESSION[$prefix][$name1][$name2])?$_SESSION[$prefix][$name1][$name2]:null;
                }else{
                    return isset($_SESSION[$prefix][$name])?$_SESSION[$prefix][$name]:null;
                }
            }else{
                if(strpos($name,'.')){
                    list($name1,$name2) =   explode('.',$name);
                    return isset($_SESSION[$name1][$name2])?$_SESSION[$name1][$name2]:null;
                }else{
                    return isset($_SESSION[$name])?$_SESSION[$name]:null;
                }
            }
        }elseif(is_null($value)){ // 删除session
            if(strpos($name,'.')){
                list($name1,$name2) =   explode('.',$name);
                if($prefix){
                    unset($_SESSION[$prefix][$name1][$name2]);
                }else{
                    unset($_SESSION[$name1][$name2]);
                }
            }else{
                if($prefix){
                    unset($_SESSION[$prefix][$name]);
                }else{
                    unset($_SESSION[$name]);
                }
            }
        }else{ // 设置session
            if(strpos($name,'.')){
                list($name1,$name2) =   explode('.',$name);
                if($prefix){
                    $_SESSION[$prefix][$name1][$name2]   =  $value;
                }else{
                    $_SESSION[$name1][$name2]  =  $value;
                }
            }else{
                if($prefix){
                    $_SESSION[$prefix][$name]   =  $value;
                }else{
                    $_SESSION[$name]  =  $value;
                }
            }
        }
        return null;
    }
    
    /**
     * 
     * 产生随机字符串
     * @access public static
     * @return string
     */
    public static function make_rand_str(){
        list( $usec ,  $sec ) =  explode ( ' ' ,  microtime ());
        $seed = (float)  $sec  + ((float)  $usec  *  100000 );
        $rpd = '';
        for($i = 0;$i<20;$i++){
            $rpd .= self::C('ROND_SEED')[mt_rand(3,strlen(self::C('ROND_SEED'))-1)];
        }
        mt_srand($seed);
        $rpd .= mt_rand();
        return $rpd;
    }

    /**
     * 产生hash字符串
     * @param $algorithm
     * @param string $str
     * @param string $ext_str
     * @return string
     */
    public static function make_hash($algorithm,$str,$ext_str = ''){
        $ctx = hash_init($algorithm);
        hash_update($ctx, $str);
        if(!empty($ext_str)){
            hash_update($ctx, $ext_str);
        }
        return hash_final($ctx);
    }
    
    public static function printr($arr){
        echo "<pre>";
        print_r($arr);
    }
    
    public static function printr_exit($arr){
        echo "<pre>";
        print_r($arr);
        exit;
    }
    
}

?>
