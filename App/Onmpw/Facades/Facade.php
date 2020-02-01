<?php


namespace Facades;

use App;

abstract class Facade
{
    private static $app;

    private static $instances = [];

    protected static function getApp()
    {
        return self::$app;
    }


    public static function bootstrapFacade(App $app)
    {
        static::$app = $app;
    }

    public static function getFacadeInstance()
    {
        return static::resolveInstance(static::getFacadeAccessor());
    }

    public static function getFacadeAccessor()
    {
        return static::getFacadeAccessObject();
    }

    public static function resolveInstance($instance)
    {
        if(is_object($instance)){
            return $instance;
        }

        if(isset(static::$instances[$instance])) {
            return static::$instances[$instance];
        }

        // todo 后期需要重新优化，改成Application Container实例化对象
        return static::$instances[$instance] = new $instance;
    }

    public static function __callStatic($method, $arguments)
    {
        $instance = static::getFacadeInstance();

        return $instance->$method(...$arguments);
    }
}