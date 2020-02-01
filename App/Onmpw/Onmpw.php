<?php

//namespace Onmpw;

use Exceptions\HandlerExceptions;
use Lib\Router;
use Lib\Request;
use Log\Logger;

/**
 * 核心类文件 用来启动整个应用程序
 *
 * @author liuhanzeng
 *
 */
class Onmpw
{

    private static $_map = array();

    private static $EXT = '.php';


    /**
     *
     * @var array
     */
    protected static $_inits = [
        HandlerExceptions::class,
        Logger::class
    ];

    protected static function Boot()
    {
        // 设置引入文件的路径
        _set_include_path(APP_PATH);

        //开启session
        session_start();

        //注册自动载入类|接口方法
        spl_autoload_register(['self', 'autoload']);
        require APP_PATH . '../vendor/autoload.php';
    }

    /**
     * 自动载入函数
     *
     * @param string $class
     *
     * @return bool
     */
    protected static function autoload($class)
    {
        self::requireFile($class);
        return true;
    }

    protected static function requireFile($class) : void
    {
        if (!isset(self::$_map[$class]) || empty(self::$_map[$class])) {
            //返回 \ 第一次出现的位置之前的字符串
            $name = strstr($class, '\\', true);

            // 如果自动加载的类 是Lib、Ext、Inter Exceptions Log Facades 中的类文件或者接口文件那么向下执行
            if ($name && in_array($name,['Lib','Ext','Inter','Exceptions','Log','Facades'])) {
                list($path,$file_name) = self::makeLibFileName($class);
            } else {
                // 是否有alias
                $aliases = require APP_PATH."Onmpw/Facades/Aliases.php";
                if(in_array($class,array_keys($aliases))){
                    list($path,$file_name) = self::makeLibFileName(class_alias($aliases[$class],$class));
                }else{
                    $pre_ext = 'Action.';
                    $class_name = str_replace('\\', '/', $class);

                    // 如果不是类库或者第三方类库里的文件的话，那么检测是否是控制器文件
                    $name = ltrim(strrchr($class_name, '/'), '/'); //查找/在字符串中最后一次出现的位置之后的字符串

                    if (preg_match('/^[A-Z]?\w*Model$/', $name)) {
                        $pre_ext = 'Model.';
                    }
                    $name = $pre_ext . str_replace(rtrim($pre_ext, '.'), '', $name);//去掉Action并且加上前缀Action.

                    $path = MODULE_PATH;  //设置路径
                    //整理文件名称
                    $file_name = str_replace(strrchr($class_name, '/'), '/' . $name . self::$EXT, $class_name);
                }
            }

            if (file_exists($path . $file_name)) {
                self::$_map[$class] = $path . $file_name;
                /** @noinspection PhpIncludeInspection */
                require self::$_map[$class];
            }
        }
        return ;
    }

    protected static function makeLibFileName($class)
    {
        $path = APP_PATH . 'Onmpw/';
        $class_name = str_replace('\\', '/', $class);
        $struct = explode('/', $class_name);
        $file_name = substr($class_name, 0, -strlen($struct[count($struct) - 1])) . $struct[count($struct) - 1] . self::$EXT;

        return [$path,$file_name];
    }

    /**
     * 开始框架
     *
     * @param App $app
     * @throws ReflectionException
     */
    protected static function start(App $app)
    {
        // 加载公共配置文件
        if (file_exists(CONFIG_PATH . 'app' . self::$EXT)) {
            Common::C(Common::LoadConf(CONFIG_PATH . 'app' . self::$EXT));
        }

        $request = Request::createFromGlobals();
        $router = $app->make(Router::class,['request'=>$request]);

        // 开始路由
        $router->router();
    }

}