<?php


namespace Lib;

use Symfony\Component\HttpFoundation\Request as HttpRequest;

class Request extends HttpRequest
{
    public function __construct(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);
    }

    public function createFromNewGlobal($parameters) {
        foreach($parameters as $key=>$val){
            $_GET[$key] = addslashes($val);
        }
        return parent::createFromGlobals();
    }
}