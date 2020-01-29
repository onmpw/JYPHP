<?php


namespace Log;

use App;
use Inter\Logger as HandlerContract;

class Logger
{
    private $handlers = [
        [FileHandler::class,true]
    ];

    private $handlerContainer = [];

    private $activeHandlers = [];

    private $debugHandler = FileHandler::class;

    /**
     * @param App $app
     * @throws \ReflectionException
     */
    public function __Init(App $app)
    {
        foreach($this->handlers as $handler){
            $this->PushHandler($app->make($handler[0]),$handler[1]);
        }
    }

    public function PushHandler(HandlerContract $handler,bool $active)
    {
        $this->handlerContainer[get_class($handler)] = $handler;
        $this->activeHandlers[get_class($handler)] = $active;
    }

    public function debug()
    {
        $this->active($this->debugHandler);
        return $this;
    }

    public function log()
    {
        $this->active();
        return $this;
    }

    public function info($message,array $context = [])
    {
        $this->handle(__FUNCTION__,$message,$context);
    }

    public function error($errMsg,array $context = [])
    {
        $this->handle(__FUNCTION__,$errMsg,$context);
    }

    private function active($handlerName = '')
    {
        if($handlerName){
            $this->activeHandlers[$handlerName] = true;
            return ;
        }
        foreach($this->activeHandlers as $handlerName=>$active){
            if(!$active) {
                $this->activeHandlers[$handlerName] = true;
            }
        }
        return ;
    }

    private function handle($method,$param,$context) : void
    {
        foreach($this->activeHandlers as $handler => $active){
            if($active) {
                call_user_func([$this->handlerContainer[$handler],$method],$param,$context);
            }
        }
    }
}