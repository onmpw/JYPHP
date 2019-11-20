<?php


namespace Exceptions;

use Exception;
use Throwable;

/**
 * Class FileNotFoundException
 * @package Exceptions
 */
class FileNotFoundException extends Exception
{
    protected $fileName = '';

    public function __construct($fileName = '',Throwable $previous = null)
    {
        $this->fileName = $fileName;
        $message = $fileName." Not Found!";
        parent::__construct($message, 404, $previous);
    }
}