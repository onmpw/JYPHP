<?php


namespace Exceptions;

use Inter\ExceptionHandler as ExceptionHandlerContract;
use Exception;
use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;

class ExceptionHandler implements ExceptionHandlerContract
{
    public function report(Exception $e)
    {
//        file_put_contents("/tmp/err.txt",$e->getMessage());
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