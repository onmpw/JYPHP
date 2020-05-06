<?php
/**
 * 定义应用根目录常量
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
 * 定义 log 目录
 */
defined('LOG_PATH') or define('LOG_PATH',DATA_PATH.'log/');

 /**
  * 定义模块目录
  */
 defined('MODULE_PATH') or define('MODULE_PATH',DOC_ROOT.'Module/');
 
 /**
  * 定义当前时间
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

// 导入应用程序管理文件
Common::Import("#/App");

use Onmpw as Kernel;
use Exceptions\ExceptionHandler;
use Inter\ExceptionHandler as ExceptionHandlerContract;
use Log\Logger;
use Facades\Facade;

class Core extends Kernel{
    public static function Boot()
    {
        // 内核初始化
        parent::Boot();

        $app = new App();

        $app->singleton(ExceptionHandlerContract::class,ExceptionHandler::class);
        $app->singleton(Logger::class);

        Facade::bootstrapFacade($app); // 开启Facade模式

        $dotEnv = Dotenv\Dotenv::createImmutable(DOC_ROOT);
        $dotEnv->load();
        // 加载需要初始化的功能

        try {
            foreach (self::$_inits as $class) {
                $obj = $app->make($class);
                call_user_func([$obj, '__Init'],$app);
            }
            parent::start($app);
        } catch (ReflectionException $e) {

        }

    }
}
