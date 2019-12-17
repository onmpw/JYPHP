<?php

namespace Exceptions;

use ReflectionException;
use Inter\ExceptionHandler as ExceptionHandlerContract;
use App;
use Exception;
use ErrorException;

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
     *
     * @param $e
     *
     * @throws ReflectionException
     */
    public function handleException(Exception $e)
    {
        try{
            $this->getExceptionHandler()->report($e);
        }catch (Exception $e){

        }

        $this->getExceptionHandler()->render($e);
    }

    public function handleShutdown()
    {
        /*$run = new Run();
        $run->prependHandler(new PrettyPageHandler);
        $run->register();

        $run->handleShutdown();*/
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