<?php

namespace Exceptions;

use App;
use Exception;
use ErrorException;

class HandlerExceptions
{
    public function _Init()
    {
        error_reporting(-1);

        set_error_handler([$this,'handleError']);

        set_exception_handler([$this,"handleException"]);

        register_shutdown_function([$this,"handleShutdown"]);

        // 关闭配置文件中的错误显示配置项
        ini_set('display_errors', 'Off');
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
        echo $file,"<br/>";
        var_dump($message);exit;
    }

    /**
     * 异常处理函数
     *
     * @param $e
     */
    public function handleException(Exception $e)
    {
        echo ("ErrorCode: ".$e->getCode()),"<br/><br/>";
        echo ("Message: ".$e->getMessage()),"<br/><br/>";
        echo $e->getTraceAsString();
    }

    public function handleShutdown()
    {
        $error = error_get_last();
        if(!is_null($error)){
            var_dump($error);
        }
    }
}