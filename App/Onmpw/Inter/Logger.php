<?php


namespace Inter;


interface Logger
{
    /**
     * @param $message
     * @param array $context
     * @return void
     */
    public function error($message,array $context): void;

    /**
     * @param $message
     * @param array $context
     * @return void
     */
    public function info($message,array $context): void;
}