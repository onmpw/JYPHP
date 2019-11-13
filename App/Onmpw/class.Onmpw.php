<?php

namespace Onmpw;
/**
 * �������ļ� ������������Ӧ�ó���
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
         * ���������ļ���·��
         */
        _set_include_path(APP_PATH);
        //����sesseion
        session_start();
        /*
         * ע���Զ�������|�ӿڷ���
         */
       spl_autoload_register('Onmpw\Onmpw::autoload');
       require APP_PATH.'../vendor/autoload.php';
    }

    /**
     * �Զ����뺯��
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
            //���� \ ��һ�γ��ֵ�λ��֮ǰ���ַ���
            $name = strstr($class, '\\' ,true);
            /*
             * ����Զ����ص��� ��Lib��Ext��Inter Exceptions �е����ļ����߽ӿ��ļ���ô����ִ��
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
                 * ������������ߵ������������ļ��Ļ�����ô����Ƿ��ǿ������ļ�
                 */
                $name = ltrim(strrchr($class_name,'/'),'/'); //����/���ַ��������һ�γ��ֵ�λ��֮����ַ���
                
                if(preg_match('/^[A-Z]?\w*Model$/',$name)){
                    $pre_ext = 'Model.';
                }
//                 $name = $pre_ext.str_replace('Action','', $name);//ȥ��Action���Ҽ���ǰ׺Action.
                $name = $pre_ext.str_replace(rtrim($pre_ext,'.'),'', $name);//ȥ��Action���Ҽ���ǰ׺Action.
                
                $path = MODULE_PATH;  //����·��
                //�����ļ�����
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
         * ���ع��������ļ�
         */
        if(file_exists(COMMON_PATH.'config'.self::$EXT)){
            \Common::C(\Common::Load_conf(COMMON_PATH.'config'.self::$EXT));
        }
        \Lib\Router::router();
    }
    
}