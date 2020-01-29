<?php


namespace Exceptions;

use Inter\ExceptionHandler as ExceptionHandlerContract;
use Exception;
use Log\Logger;
use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;
use App;

class ExceptionHandler implements ExceptionHandlerContract
{
    private $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * @param Exception $e
     * @throws \ReflectionException
     */
    public function report(Exception $e)
    {
        $logger = $this->app->make(Logger::class);
        $logger->log()->error($e->getMessage().$e->getTraceAsString());
    }

    public function render(Exception $e)
    {
        $this->renderExceptionWithWhoops($e);
    }

    /**
     * 使用 whoops 渲染exception到页面
     *
     * @param $e
     *
     * @return string
     */
    protected function renderExceptionWithWhoops(Exception $e) : string
    {
        $run = new Run();
        $run->prependHandler(new PrettyPageHandler);
        $run->register();

        return $run->handleException($e);
    }

    public function renderForConsole(Exception $e)
    {
        // TODO: Implement renderForConsole() method.
    }
}