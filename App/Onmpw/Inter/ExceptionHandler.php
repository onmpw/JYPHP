<?php


namespace Inter;

use Exception;

interface ExceptionHandler
{
    /**
     * Report or log an exception.
     *
     * @param  Exception  $e
     * @return void
     */
    public function report(Exception $e);

    /**
     * Render an exception into an HTTP response.
     *
     * @param Exception $e
     * @return bool
     */
    public function render(Exception $e):bool;
}