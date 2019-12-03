<?php


namespace Exceptions;

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
        if(is_array($router)){
            $router = json_encode($router);
        }

        $this->router = $router;
    }

    public function getRouter()
    {
        return $this->router;
    }
}