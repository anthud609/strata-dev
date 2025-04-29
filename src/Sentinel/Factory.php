<?php
/**
 * src/Sentinel/Factory.php
 *
 * Factory class responsible for constructing and configuring the PSR-3 Logger
 * instance using Monolog. It reads channel definitions from the configuration,
 * sets up handlers with formatters, and attaches any custom processors.
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

class Factory
{
    /**
     * Create and return a configured Logger instance.
     *
     * @param array $config The Sentinel configuration array.
     *                      Must include 'channels' => ['default' => [...]].  
     * @return Logger       The configured Monolog Logger.
     */
    public static function create(array $config): Logger
    {
        // Retrieve the default channel's configuration or fallback to empty array
        $channelConfig = $config['channels']['default'] ?? [];

        // Instantiate the Logger with a default channel name
        $name   = $channelConfig['name'] ?? 'default';
        $logger = new Logger($name);

        //
        // 1) Configure handlers
        //
        foreach ($channelConfig['handlers'] ?? [] as $handlerConfig) {
            switch ($handlerConfig['type'] ?? '') {
                case 'stream':
                    $handler = new StreamHandler(
                        $handlerConfig['path'],
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
                    // Immutable, write-once log storage handler
                    $handler = new WormHandler(
                        $config['worm']['storage_path']   ?? '',
                        $config['worm']['checksum_table'] ?? '',
                        $config['worm']['algorithm']      ?? 'sha256'
                    );
                    break;

                case 'webhook':
                    // Generic HTTP-notify handler
                    $routeKey = $handlerConfig['route'] ?? null;
                    $webhook  = $config['webhooks'][$routeKey] ?? [];
                    $handler  = new WebhookHandler(
                        $webhook['url']     ?? '',
                        $handlerConfig['method'] ?? 'POST'
                    );
                    break;

                // TODO: add cases for 'email', 'syslog', 'mongodb', etc.

                default:
                    // Unknown handler type; skip
                    continue 2;
            }

            $logger->pushHandler($handler);
        }

        //
        // 2) Attach processors
        //
        foreach ($channelConfig['processors'] ?? [] as $procConfig) {
            $class = $procConfig['class'] ?? null;
            if (! $class || ! class_exists($class)) {
                continue;
            }
            // Pass the full config so processors can pick their own settings
            $processor = new $class($config);
            $logger->pushProcessor($processor);
        }

        return $logger;
    }
}
