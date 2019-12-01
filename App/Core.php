<?php
/**
 * 定义应用根目录常量
 * @var APP_PATH
 */
defined('DOC_ROOT') or define('DOC_ROOT',$_SERVER['DOCUMENT_ROOT'].'/');

/**
 * 定义服务目录
 */
defined('SERVICE_PATH') or define('SERVICE_PATH',DOC_ROOT.'Service/');
/**
 * 定义应用主程序目录
 */
defined('APP_PATH') or define('APP_PATH',DOC_ROOT.'App/');
/**
 * 定义接口路径
 */
defined('INTERFACE_PATH') or define('INTERFACE_PATH',APP_PATH.'Onmpw/Interface/');
/**
 * 定义类库目录
 */
defined('LIB_PATH') or define('LIB_PATH',APP_PATH.'Onmpw/Lib/');

/**
 * 定义第三方扩展目录
 */
 defined('EXT_PATH') or define('EXT_PATH',APP_PATH."Onmpw/Ext/");

/**
 * 定义公共目录
 */
 defined('COMMON_PATH') or define('COMMON_PATH',APP_PATH ."Common/");

/**
 * 定义配置文件目录
 */
 defined('CONFIG_PATH') or define('CONFIG_PATH',APP_PATH ."Config/");

 /**
  * 定义 data目录
  */
 defined('DATA_PATH') or define('DATA_PATH',DOC_ROOT.'Data/');
 
 /**
  * 定义模块目录
  */
 defined('MODULE_PATH') or define('MODULE_PATH',DOC_ROOT.'Module/');
 
 /**
  * 定义当前时间
  * @var unknown
  */
 define('NOW_TIME',      $_SERVER['REQUEST_TIME']);
 

 /**
  * 引入公共类(Common) 文件 和公共函数库文件
  */
 include COMMON_PATH."Common.php";
/**
 * 定义设置载入文件的路径函数
 * @param string $path
 */
function _set_include_path($path = ''){
    if(!empty($path)){
        if($path != get_include_path()){
            if(is_dir($path)){
                set_include_path($path);
            }
        }
    }
}

/**
 * 导入 启动类文件 Onmpw
 */
Common::Import("#/Onmpw/Onmpw");
use Onmpw as Kernel;
class Core extends Kernel{
    public static function _Init()
    {
        // 内核初始化
        parent::_Init();

        // 加载需要初始化的功能
        foreach(self::$_inits as $class){
            call_user_func([new $class,'_Init']);
        }
    }
}
