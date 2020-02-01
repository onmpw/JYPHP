<?php


namespace Facades;


use Log\Logger;

class Log extends Facade
{
    public static function getFacadeAccessObject()
    {
        return self::getApp()->make(Logger::class);
    }
}