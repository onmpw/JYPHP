<?php


namespace Exceptions;

use Throwable;

class FatalThrowableError extends FatalErrorException
{
    private $originalClassName;

    public function __construct(Throwable $e)
    {
        $this->originalClassName = get_class($e);

        if($e instanceof \ParseError){
            $severity = E_PARSE;
        }elseif($e instanceof \TypeError){
            $severity = E_RECOVERABLE_ERROR;
        }else{
            $severity = E_ERROR;
        }

        parent::__construct($e->getMessage(),$e->getCode(),$severity,$e->getFile(),$e->getLine(),$e->getPrevious());

        $this->setTrace($e->getTrace());
    }
}