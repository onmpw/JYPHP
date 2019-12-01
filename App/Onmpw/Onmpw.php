<?php

//namespace Onmpw;

use Exceptions\FileNotFoundException;
use Exceptions\HandlerExceptions;
use Lib\Router;

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
        HandlerExceptions::class
    ];

    protected static function _Init()
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
        $after_ext = '.php';
        if (!isset(self::$_map[$class]) || empty(self::$_map[$class])) {
            //返回 \ 第一次出现的位置之前的字符串
            $name = strstr($class, '\\', true);

            // 如果自动加载的类 是Lib、Ext、Inter Exceptions 中的类文件或者接口文件那么向下执行
            if (in_array($name, array('Lib', 'Ext', 'Inter', 'Exceptions'))) {
                $class_name = str_replace('\\', '/', $class);
                $path = APP_PATH . 'Onmpw/';
                $struct = explode('/', $class_name);
                $file_name = substr($class_name, 0, -strlen($struct[count($struct) - 1])) . $struct[count($struct) - 1] . $after_ext;
            } else {
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
                $file_name = str_replace(strrchr($class_name, '/'), '/' . $name . $after_ext, $class_name);
            }

            if (file_exists($path . $file_name)) {
                self::$_map[$class] = $path . $file_name;
                /** @noinspection PhpIncludeInspection */
                require self::$_map[$class];
            }
        }

        return true;

    }

    public static function start()
    {
        /*
         * 加载公共配置文件
         */
        if (file_exists(CONFIG_PATH . 'app' . self::$EXT)) {
            \Common::C(\Common::Load_conf(CONFIG_PATH . 'app' . self::$EXT));
        }

        // 开始路由
        Router::router();
    }

}