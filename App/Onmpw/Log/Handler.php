<?php


namespace Log;

use Monolog\Logger as BaseLogger;
class Handler
{

    protected $logger;

    public function __construct()
    {
        $this->logger = new BaseLogger(static::getChannelName());
    }

    protected function pushHandler($handler)
    {
        $this->logger->pushHandler($handler);
    }

    protected function registerHandler(array $handlers)
    {
        foreach ($handlers  as $handler){
            $handler->setFormatter(static::getFormatter());
            $this->pushHandler($handler);
        }
    }

    public function info($message,array $context)
    {
        $this->logger->info($this->formatMessage($message),$context);
    }

    public function error($errMessage,array $context)
    {
        $this->logger->error($this->formatMessage($errMessage),$context);
    }

    /**
     * Format the parameters for the logger.
     *
     * @param  mixed  $message
     * @return mixed
     */
    protected function formatMessage($message)
    {
        if (is_array($message)) {
            return var_export($message, true);
        }

        return $message;
    }
}