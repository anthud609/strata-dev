<?php
// src/Sentinel/Facade.php

namespace Sentinel;

/**
 * Facade for the Sentinel Logger.
 *
 * Acts as a static proxy to the PSR-3 Logger instance created by Sentinel\Factory.
 * After bootstrapping, all static calls on this Facade forward to the underlying logger.
 *
 * Example:
 *     Sentinel\Facade::info('User login', ['user_id' => $userId]);
 *
 * @see \Psr\Log\LoggerInterface
 */
class Facade
{
    /**
     * The underlying PSR-3 logger instance once bootstrapped.
     *
     * @var \Psr\Log\LoggerInterface|null
     */
    private static $instance;

    /**
     * Register the PSR-3 logger with the Facade.
     *
     * This must be called during application bootstrap (e.g. in bootstrap/sentinel.php)
     * to make logging available via Sentinel\Facade::{level}().
     *
     * @param \Psr\Log\LoggerInterface $logger The logger instance to proxy.
     * @return void
     */
    public static function setInstance(\Psr\Log\LoggerInterface $logger): void
    {
        self::$instance = $logger;
    }

    /**
     * Magic static method handler.
     *
     * Forwards any static call (e.g. info, error, debug) to the underlying logger.
     * Throws if the Facade has not been bootstrapped with setInstance().
     *
     * @param string $method The log level or custom method name.
     * @param array  $args   Arguments to pass to the logger method (message, context).
     * @return mixed         The return value of the proxied logger method.
     *
     * @throws \RuntimeException If no logger instance has been registered.
     */
    public static function __callStatic(string $method, array $args)
    {
        if (!self::$instance) {
            // Prevent silent failures: ensure bootstrap has run before logging.
            throw new \RuntimeException('Sentinel not bootstrapped');
        }

        // Forward method call and arguments to the PSR-3 logger.
        return self::$instance->{$method}(...$args);
    }
}
