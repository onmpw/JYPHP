<?php
namespace Log;


use Inter\Logger as LoggerContract;
use App;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as BaseLogger;

class FileHandler extends Handler implements LoggerContract
{
    private $app;

    private $fileNameFormat = 'Y-m-d';

    /**
     * FileHandler constructor.
     * @param App $app
     */
    public function __construct(App $app)
    {
        parent::__construct();
        $this->app = $app;
    }

    protected function getChannelName()
    {
        return "JYPHP_LOG";
    }

    /**
     * @param $message
     * @param array $context
     * @return void
     * @throws \Exception
     */
    public function error($message,array $context): void
    {
        $fileName = "JYPHP_E"."_".date($this->fileNameFormat);
        $this->registerHandler([new StreamHandler(LOG_PATH.$fileName.'.log',BaseLogger::ERROR)]);

        parent::error($message,$context);

    }

    /**
     * @param $message
     * @param array $context
     * @return void
     * @throws \Exception
     */
    public function info($message,array $context): void
    {
        $fileName = "JYPHP_I"."_".date($this->fileNameFormat);
        $this->registerHandler([new StreamHandler(LOG_PATH.$fileName.'.log',BaseLogger::INFO)]);

        parent::info($message,$context);
    }

    protected function getFormatter()
    {
        return new LineFormatter(null,null,true,true);
    }
}