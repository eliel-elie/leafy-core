<?php

namespace LeafyTech\Core\Support\Log;

/**
 * @method static emergency(string $message, array $context = []): void
 * @method static alert(string $message, array $context = []): void
 * @method static critical(string $message, array $context = []): void
 * @method static error(string $message, array $context = []): void
 * @method static warning(string $message, array $context = []): void
 * @method static notice(string $message, array $context = []): void
 * @method static info(string $message, array $context = []): void
 * @method static debug(string $message, array $context = []): void
 */
class Log
{
    private static ?Logger $instance = null;

    public static function getInstance(): Logger
    {
        if (self::$instance === null) {
            self::$instance = new Logger();
        }

        return self::$instance;
    }

    public static function setInstance(Logger $logger): void
    {
        self::$instance = $logger;
    }

    /**
     * Handle dynamic, static calls to the Logger instance.
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic(string $method, array $arguments)
    {
        $instance = self::getInstance();
        return call_user_func_array(array($instance, $method), $arguments);
    }
}