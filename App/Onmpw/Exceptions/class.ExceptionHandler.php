<?php


namespace Exceptions;

use Inter\ExceptionHandler as ExceptionHandlerContract;
use Exception;

class ExceptionHandler implements ExceptionHandlerContract
{
    public function report(Exception $e)
    {

    }

    public function render(Exception $e): bool
    {
        // TODO: Implement render() method.
    }
}