<?php

namespace Kapi\Log;

use InvalidArgumentException;
use Kapi\Log\Logger\File;
use Psr\Log\LogLevel;

class Log
{
    private const LEVELS = [
        LogLevel::EMERGENCY,
        LogLevel::ALERT,
        LogLevel::CRITICAL,
        LogLevel::ERROR,
        LogLevel::WARNING,
        LogLevel::NOTICE,
        LogLevel::INFO,
        LogLevel::DEBUG
    ];

    private function __construct()
    {
    }

    public static function emergency($message, array $context = [])
    {
        static::log(LogLevel::EMERGENCY, $message, $context);
    }

    public static function alert($message, array $context = [])
    {
        static::log(LogLevel::ALERT, $message, $context);
    }

    public static function critical($message, array $context = [])
    {
        static::log(LogLevel::CRITICAL, $message, $context);
    }

    public static function error($message, array $context = [])
    {
        static::log(LogLevel::ERROR, $message, $context);
    }

    public static function warning($message, array $context = [])
    {
        static::log(LogLevel::WARNING, $message, $context);
    }

    public static function notice($message, array $context = [])
    {
        static::log(LogLevel::NOTICE, $message, $context);
    }

    public static function info($message, array $context = [])
    {
        static::log(LogLevel::INFO, $message, $context);
    }

    public static function debug($message, array $context = [])
    {
        static::log(LogLevel::DEBUG, $message, $context);
    }

    public static function log($level, $message, array $context = [])
    {
        if (!in_array($level, static::LEVELS)) {
            throw new InvalidArgumentException(sprintf('Invalid log level "%s"', $level));
        }

        $logger = new File();
        $logger->log($level, $message, $context);
    }
}