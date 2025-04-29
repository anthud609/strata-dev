<?php
/**
 * src/Sentinel/Factory.php
 *
 * Factory class responsible for constructing and configuring the PSR-3 Logger
 * instance using Monolog. It also provides PHP error/exception handlers that
 * funnel into the same logger.
 *
 * @package Sentinel
 * @see \Monolog\Logger
 */

namespace Sentinel;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Sentinel\Handler\WormHandler;
use Sentinel\Handler\WebhookHandler;
use Throwable;

class Factory
{
    /**
     * Create and return a configured Logger instance.
     *
     * @param array $config The Sentinel configuration array.
     *                      Must include 'channels' => ['default' => [...]]
     * @return Logger       The configured Monolog Logger.
     */
    public static function create(array $config): Logger
    {
        // 1) Grab default channel config (or empty array)
        $channelConfig = $config['channels']['default'] ?? [];

        // 2) Instantiate the Logger with configured name or default
        $name   = $channelConfig['name'] ?? 'default';
        $logger = new Logger($name);

        // 3) Configure each handler
        foreach ($channelConfig['handlers'] ?? [] as $handlerConfig) {
            $type = $handlerConfig['type'] ?? '';
            switch ($type) {
                case 'stream':
                    $handler = new StreamHandler(
                        $handlerConfig['path'] ?? 'php://stdout',
                        Logger::toMonologLevel($handlerConfig['level'] ?? 'DEBUG')
                    );
                    $formatter = new LineFormatter(
                        $handlerConfig['format'] ?? null,
                        null,
                        true,
                        true
                    );
                    $handler->setFormatter($formatter);
                    break;

                case 'worm':
                    $handler = new WormHandler(
                        $config['worm']['storage_path']   ?? '',
                        $config['worm']['checksum_table'] ?? '',
                        $config['worm']['algorithm']      ?? 'sha256'
                    );
                    break;

                case 'webhook':
                    $routeKey = $handlerConfig['route'] ?? '';
                    $webhook  = $config['webhooks'][$routeKey] ?? [];
                    $handler  = new WebhookHandler(
                        $webhook['url']    ?? '',
                        $handlerConfig['method'] ?? 'POST'
                    );
                    break;

                default:
                    // unrecognized handler type
                    continue 2;
            }

            $logger->pushHandler($handler);
        }

        // 4) Attach each processor
        foreach ($channelConfig['processors'] ?? [] as $procConfig) {
            $className = $procConfig['class'] ?? '';
            if (! $className || ! class_exists($className)) {
                continue;
            }
            $processor = new $className($config);
            $logger->pushProcessor($processor);
        }

        return $logger;
    }

    /**
     * PHP error handler callback.
     * Normalizes PHP errors into a log record.
     *
     * @return bool  true = bypass PHP internal handler; false = continue
     */
    public static function handlePhpError(int $errno, string $errstr, string $errfile, int $errline): bool
    {
        $message = sprintf('PHP Error [%d]: %s', $errno, $errstr);
        // log at ERROR level
        Facade::error($message, [
            'file'  => $errfile,
            'line'  => $errline,
            'errno' => $errno,
        ]);
        // return true to prevent PHP internal handler from running
        return true;
    }

    /**
     * Exception handler callback.
     * Logs uncaught exceptions.
     */
    public static function handleException(Throwable $e): void
    {
        $message = $e->getMessage();
        Facade::error($message, [
            'exception' => get_class($e),
            'file'      => $e->getFile(),
            'line'      => $e->getLine(),
            'trace'     => $e->getTraceAsString(),
        ]);
    }

    /**
     * Shutdown handler to catch fatal errors.
     */
    public static function handleShutdown(): void
    {
        $err = error_get_last();
        if (! $err) {
            return;
        }

        // Only handle fatal-level errors
        $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR];
        if (in_array($err['type'], $fatalTypes, true)) {
            self::handlePhpError(
                $err['type'],
                $err['message'],
                $err['file']  ?? 'n/a',
                $err['line']  ?? 0
            );
        }
    }
}
