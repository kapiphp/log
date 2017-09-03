<?php

namespace Kapi\Log;

class Log
{
    /**
     * Log constructor.
     */
    private function __construct()
    {
    }

    public static function emergency($message, array $context = [])
    {
        static::log(__FUNCTION__, $message, $context);
    }

    public static function alert($message, array $context = [])
    {
        static::log(__FUNCTION__, $message, $context);
    }

    public static function critical($message, array $context = [])
    {
        static::log(__FUNCTION__, $message, $context);
    }

    public static function error($message, array $context = [])
    {
        static::log(__FUNCTION__, $message, $context);
    }

    public static function warning($message, array $context = [])
    {
        static::log(__FUNCTION__, $message, $context);
    }

    public static function notice($message, array $context = [])
    {
        static::log(__FUNCTION__, $message, $context);
    }

    public static function info($message, array $context = [])
    {
        static::log(__FUNCTION__, $message, $context);
    }

    public static function debug($message, array $context = [])
    {
        static::log(__FUNCTION__, $message, $context);
    }

    public static function log($level, $message, array $context = [])
    {
        // TODO: Implement log() method.
    }
}