<?php


namespace Lib;

use Exception;
use Throwable;

class RouterException extends Exception
{
    protected $router;

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function setRouter($router)
    {
        $this->router = $router;
    }

    public function getRouter()
    {
        return $this->router;
    }
}