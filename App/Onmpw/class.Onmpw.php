<?php

namespace Onmpw;
/**
 * 核心类文件 用来启动整个应用程序
 * 
 * @author onmpw
 *
 */
class Onmpw{
    
    private static $_instance = array();
    
    private static $_map = array();
    
    private static $EXT = '.php';
    
    public function Run(){
        
    }
    
    public static function _Init(){
        /*
         * 设置引入文件的路径
         */
        _set_include_path(APP_PATH);
        //开启sesseion
        session_start();
        /*
         * 注册自动载入类|接口方法
         */
       spl_autoload_register('Onmpw\Onmpw::autoload');
       require APP_PATH.'../vendor/autoload.php';
    }

    /**
     * 自动载入函数
     *
     * @param string $class
     *
     * @return bool
     */
    public static function autoload($class){
        $pre_ext = '';
        $after_ext = '.php';
        if(isset(self::$_map[$class]) && !empty(self::$_map[$class])){
            require self::$_map[$class];
        }else{
            //返回 \ 第一次出现的位置之前的字符串
            $name = strstr($class, '\\' ,true);
            /*
             * 如果自动加载的类 是Lib、Ext、Inter Exceptions 中的类文件或者接口文件那么向下执行
             */
            if(in_array($name,array('Lib','Ext','Inter','Exceptions'))){
//              $class = str_replace('\\','/',substr($class,strpos($class,'\\')));
                $class_name = str_replace('\\','/',$class);
                //Common::Import($class);
                if($name == 'Inter'){
                    $pre_ext = 'interface.';
                }else{
                    $pre_ext = 'class.';
                }
                $path = APP_PATH.'Onmpw/';
                $struct = explode('/',$class_name);
                $class_name = substr($class_name,0,-strlen($struct[count($struct)-1])).$pre_ext.$struct[count($struct)-1].$after_ext;
                if(file_exists($path.$class_name)){
                    require $path.$class_name;
                    self::$_map[$class] = $path.$class_name;
                }
            }else{//if(in_array($name,\Common::C('MODULE'))){
                $pre_ext = 'Action.';
                $class_name = str_replace('\\','/',$class);
                /*
                 * 如果不是类库或者第三方类库里的文件的话，那么检测是否是控制器文件
                 */
                $name = ltrim(strrchr($class_name,'/'),'/'); //查找/在字符串中最后一次出现的位置之后的字符串
                
                if(preg_match('/^[A-Z]?\w*Model$/',$name)){
                    $pre_ext = 'Model.';
                }
//                 $name = $pre_ext.str_replace('Action','', $name);//去掉Action并且加上前缀Action.
                $name = $pre_ext.str_replace(rtrim($pre_ext,'.'),'', $name);//去掉Action并且加上前缀Action.
                
                $path = MODULE_PATH;  //设置路径
                //整理文件名称
                $file_name = str_replace(strrchr($class_name,'/'),'/'.$name.$after_ext,$class_name);
                
                if(file_exists($path.$file_name)){
                    require $path.$file_name;
                    self::$_map[$class] = $path.$file_name;
                }
            }
           
            
        } 
        return true;
            
    }
    
    public static function start(){
        /*
         * 加载公共配置文件
         */
        if(file_exists(COMMON_PATH.'config'.self::$EXT)){
            \Common::C(\Common::Load_conf(COMMON_PATH.'config'.self::$EXT));
        }
        \Lib\Router::router();
    }
    
}