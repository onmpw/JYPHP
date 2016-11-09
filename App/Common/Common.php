<?php

/**
 * 
 * @author Onmpw
 *
 */
class Common{
   
    /*
     * ���Ļ���
     */
    private static $_class = array();
    
    private static $_interface = array();
    
    private static $_files = array();
    
    /*
     * �����ļ��еĻ���
     */
    private static $_confs = array();
    
    /*
     * ���������Ļ���
     */
    private static $_ext = array();
    
    const EXT = 'php';
    
    /**
     * ������⺯��
     * �����ͷ�� @|# ��ʾ��������Ǵ������ռ����
     * # �������ʼ���� 
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
         * ���ȼ��Ҫ�������֮ǰ�Ƿ������
         */
        if(isset(self::$_class[$name.'-'.$dir]))
            return true;
        else
            self::$_class[$name.'-'.$dir] = true;
        
        /*
         * ����������ļ�������һ���ļ�
         */
        $class_struct = explode('/',$name);
        
        $ft = '';  //��ʼ�������ļ����ͱ���
        
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
     * ������������
     * ���$ext�д��� @ ��ô@����ı�ʾ�ļ����ĺ�׺  ����չ��֮ǰ�����ļ���֮��
     * ���$ext�д��� # ��ô#����ı�ʾ�ļ�����ǰ׺  ���ļ���֮ǰ
     * 
     * @param string $ext
     * $ext ������� : ��ô : �������·��  ���һ��: ֮������ļ���
     * 
     * @return boolean
     */
    public static function Ext($ext = ''){
        if(empty($ext)) return false;
        if(isset(self::$_ext[$ext])){
            return true; 
        }
        
        /*
         * ����������ʽƥ�䵱ǰ������Ϣ
         * ������� һ����    �ַ���@|#�ļ�����
         * һ����    �ַ���
         * 
         * �����������false
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
        
        //�����Ƿ����:
        // : ��ʾ / Ҳ���Ƿָ�·��
        if(strpos($finfo,':')){
            $fname = ltrim(strrchr($finfo,':'),':'); //�õ��ļ����� ���һ�� : ֮����ַ���
            $finfo = substr($finfo,0,strrpos($finfo,':'));  //�õ����һ��: ֮ǰ���ַ��� �����ļ�����·����Ϣ
        }else{
            $fname = $finfo;
        }
        //�� : �滻�� /
        $finfo = str_replace(':','/',$finfo).'/';
        
        if(strpos($ext,'@'))
            $fname = $fname.'.'.$fext.self::EXT;  // ����� @ ��ô �ļ���չ��Ϣ ���ļ���֮�� ��չ��֮ǰ
        elseif(strpos($ext,'#')) 
            $fname = $fext.$fname.".".self::EXT; //�����# ��ô�ļ���չ��Ϣ���ļ���֮ǰ
        else
            $fname = $fname.'.'.self::EXT;  //���һ������� û����չ��Ϣ
        return self::Require_file(EXT_PATH.$finfo.$fname);
    }
    
    /**
     * �����ļ�����
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
     * ��������������ú���
     * �����ж����������Ļ����������$_confs ��
     * ������ַ�����˵����ȡ����ֵ ������� ��ֱ�ӷ��أ���������в����� ��ô�������ļ��в��� 
     * ��ά����������ҷ�ʽ
     * C(name:name-key)
     * @param mixed $confs
     * @return mixed
     */
    public static function C($confs){
        /*
         * �����жϲ���������
         */
        if(is_array($confs)){
            //�������������� ���뻺������
            foreach($confs as $key=>$val){
                self::$_confs[$key] = $val;
            }
            return '';
        }
        if(is_string($confs)){
            //�����������ַ��� ��ʼ���в��Ҳ���
            if(strpos($confs,':')){
                //���Ҷ�ά�������������
                $confs = explode(':', $confs);
                if (count($confs) == 2) {
                    /*
                     * ��� ������������С��2 ˵���Ƕ�ά��������
                     * ��������д��� ֱ�ӷ���
                     */
                    if (isset(self::$_confs[$confs[0]]) && is_array(self::$_confs[$confs[0]])) {
                        if (isset(self::$_confs[$confs[0]][$confs[1]]))
                            return self::$_confs[$confs[0]][$confs[1]];
                    }
                    /*
                     * ���������в����� ��ô���ȼ�鵱ǰģ��������ļ����Ƿ����
                     */
                    if(file_exists(MODULE_PATH.MODULE_NAME.'/Common/config.php')){
                        $C = require(MODULE_PATH.MODULE_NAME.'/Common/config.php');
                        if(isset($C[$confs[0]][$confs[1]])){
                            self::$_confs[$confs[0]][$confs[1]] = $C[$confs[0]][$confs[1]];
                            return $C[$confs[0]][$confs[1]];
                        }
                    }
                    /*
                     * �����ǰ�����в����� ����ҹ��������ļ����Ƿ����
                     */
                    $C = require(COMMON_PATH.'config.php');
                    if(isset($C[$confs[0]][$confs[1]])){
                        self::$_confs[$confs[0]][$confs[1]] = $C[$confs[0]][$confs[1]];
                        return $C[$confs[0]][$confs[1]];
                    };
                    return '';
                }
                return '';
            }
            
            if(isset(self::$_confs[$confs])) return self::$_confs[$confs];
            /*
             * ���������в����� ��ô���ȼ�鵱ǰģ��������ļ����Ƿ����
             */
            if(file_exists(MODULE_PATH.MODULE_NAME.'/Common/config.php')){
                $C = require(MODULE_PATH.MODULE_NAME.'/Common/config.php');
                if(isset($C[$confs])){
                    self::$_confs[$confs] = $C[$confs];
                    return $C[$confs];
                };
            }
            /*
             * �����ǰ�����в����� ����ҹ��������ļ����Ƿ����
             */
            $C = require(COMMON_PATH.'config.php');
            if(isset($C[$confs])){
                self::$_confs[$confs] = $C[$confs];
                return $C[$confs];
            }
            return '';
        }
    }
    
    /**
     * ���������ļ�����
     * 
     * @param string $conf_file
     * 
     * @return mixed
     */
    public static function Load_conf($conf_file = ''){
        if(empty($conf_file)){
            return require_once COMMON_PATH.'config.php';
        }
        if(file_exists($conf_file)){
            return require_once $conf_file;
        }
        return false;
    }
    
    /**
     * ����url�е� ģ�顢������������������������
     * 
     * @param string $val
     * @param string $key
     */
    public static function Url_filter(&$val,$key){
        if(in_array($key,array_values(self::C('URL'))))
            $val = '';
    }
    /**
     * ɾ��������ֵΪ�յı���
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
     * �����ַ�������
     * @access public static
     * @param string $val
     * @return string
     */
    public static function escapeString($val){
        return addslashes($val);
    }
    
    /**
     * ����smarty �����ĸ�ʽ
     * 
     * @param object $tplname
     */
    public static function smarty_constant_filter($tplname){
        $str = preg_replace('/__([a-zA-Z]*)__/', '{$smarty.const.\\1}', $tplname);
        return $str;
    }
    
    /**
     * ��д $_GET
     * @param string $key
     * @return boolean|Ambigous <>
     */
    public static function get($key = ''){
        static $data = array();
        if(empty($key)) return false; //���$key Ϊ�� �򷵻� false
        if(isset($data[$key])) return $data[$key];  //��� ��ǰ��������ȷ ���ؿ�
        if(isset($_GET)){
            $data = $_GET;
            unset($_GET);
        }
        foreach($data as $k=>$v){
            $data[$k] = addslashes($v);
        }
        if(!isset($data[$key])) return false;
        return $data[$key];
    }
    
    /**
     * ��д $_POST
     * @param string $key
     * @return boolean|Ambigous <>
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
     * session������
     * @param string|array $name session���� ���Ϊ�������ʾ����session����
     * @param mixed $value sessionֵ
     * @return mixed
     */
    public static function session($name='',$value='') {
        $prefix   =  self::C('SESSION_PREFIX');
        if(is_array($name)) { // session��ʼ�� ��session_start ֮ǰ����
            if(isset($name['prefix'])) self::C('SESSION_PREFIX',$name['prefix']);
            if(self::C('VAR_SESSION_ID') && isset($_REQUEST[self::C('VAR_SESSION_ID')])){
                session_id($_REQUEST[self::C('VAR_SESSION_ID')]);
            }elseif(isset($name['id'])) {
                session_id($name['id']);
            }
            if('common' == APP_MODE){ // ����ģʽ���ܲ�֧��
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
            if(isset($name['type']))            self::C('SESSION_TYPE',$name['type']);
            if(self::C('SESSION_TYPE')) { // ��ȡsession����
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
            // ����session
            if(self::C('SESSION_AUTO_START'))  session_start();
        }elseif('' === $value){
            if(''===$name){
                // ��ȡȫ����session
                return $prefix ? $_SESSION[$prefix] : $_SESSION;
            }elseif(0===strpos($name,'[')) { // session ����
                if('[pause]'==$name){ // ��ͣsession
                    session_write_close();
                }elseif('[start]'==$name){ // ����session
                    session_start();
                }elseif('[destroy]'==$name){ // ����session
                    $_SESSION =  array();
                    session_unset();
                    session_destroy();
                }elseif('[regenerate]'==$name){ // ��������id
                    session_regenerate_id();
                }
            }elseif(0===strpos($name,'?')){ // ���session
                $name   =  substr($name,1);
                if(strpos($name,'.')){ // ֧������
                    list($name1,$name2) =   explode('.',$name);
                    return $prefix?isset($_SESSION[$prefix][$name1][$name2]):isset($_SESSION[$name1][$name2]);
                }else{
                    return $prefix?isset($_SESSION[$prefix][$name]):isset($_SESSION[$name]);
                }
            }elseif(is_null($name)){ // ���session
                if($prefix) {
                    unset($_SESSION[$prefix]);
                }else{
                    $_SESSION = array();
                }
            }elseif($prefix){ // ��ȡsession
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
        }elseif(is_null($value)){ // ɾ��session
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
        }else{ // ����session
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
     * ��������ַ���
     * @access public static
     * @return number
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
     * ����hash�ַ���
     * @param string $algoithm
     * @param string $str
     * @param string $extr_str
     * @return string
     */
    public static function make_hash($algorithm,$str,$extr_str = ''){
        $ctx = hash_init($algorithm);
        hash_update($ctx, $str);
        if(!empty($extr_str)){
            hash_update($ctx, $extr_str);
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
