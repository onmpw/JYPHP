<?php

namespace Exceptions;

use Exception;
use ErrorException;

class HandlerExceptions
{
    public function _Init()
    {
        error_reporting(-1);

        set_error_handler([$this,'handleError']);

        set_exception_handler([$this,"handleException"]);
    }

    /**
     * 对未捕获的错误进行处理
     *
     * @param $level
     * @param $message
     * @param string $file
     * @param int $line
     * @param array $context
     * @throws ErrorException
     */
    public function handleError($level, $message, $file = '', $line = 0, $context = [])
    {
        if(error_reporting() & $level){
            throw new ErrorException($message,0,$level,$file,$line);
        }
    }

    /**
     * 异常处理函数
     *
     * @param $e
     */
    public function handleException(Exception $e)
    {
        var_dump($e->getCode());
        var_dump($e->getMessage());
    }
}