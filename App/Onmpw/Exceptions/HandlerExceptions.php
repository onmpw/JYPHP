<?php

namespace Exceptions;

use ReflectionException;
use Inter\ExceptionHandler as ExceptionHandlerContract;
use App;
use Exception;
use ErrorException;
use Throwable;

class HandlerExceptions
{
    /**
     * @var App
     */
    protected $app;

    public function _Init(App $app)
    {
        $this->app = $app;

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
     *
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
     * 对未使用`try{}catch(){}` 捕获的异常进行处理
     *
     * @param $e
     *
     * @throws ReflectionException
     */
    public function handleException($e)
    {
        if(! $e instanceof Exception) {
            $e = new FatalThrowableError($e);
        }

        try{
            $this->getExceptionHandler()->report($e);
        }catch (Exception $e){

        }

        $this->getExceptionHandler()->render($e);
    }

    /**
     * 程序终止处理
     *
     * @throws ReflectionException
     */
    public function handleShutdown()
    {
        $error = error_get_last();
        if(!is_null($error)) {
            // 发生了错误导致程序异常终止
            $this->handleException(new FatalErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']));
        }
    }

    /**
     * 获取异常处理器
     *
     * @return ExceptionHandlerContract
     *
     * @throws ReflectionException
     */
    protected function getExceptionHandler()
    {
        return $this->app->make(ExceptionHandlerContract::class);
    }
}