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

/**
 * Class Factory
 *
 * Builds and configures Monolog channels, handlers, and formatters based
 * on the provided configuration array.
 */
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
        $logger = new Logger('default');

        // Iterate through each handler defined in the channel config
        foreach ($channelConfig['handlers'] ?? [] as $handlerConfig) {
            if ($handlerConfig['type'] === 'stream') {
                // Create a StreamHandler to write logs to a file or stream
                $streamHandler = new StreamHandler(
                    $handlerConfig['path'],                          // log file path
                    Logger::toMonologLevel($handlerConfig['level'])   // log level
                );

                // Attach a LineFormatter to control log message format
                $formatter = new LineFormatter(
                    $handlerConfig['format'],    // format template
                    null,                        // date format (default)
                    true,                        // allow inline line breaks
                    true                         // ignore empty context and extra
                );
                $streamHandler->setFormatter($formatter);

                // Push the configured handler onto the Logger stack
                $logger->pushHandler($streamHandler);
            }

            // TODO: handle other handler types (webhook, WORM, email, etc.)
        }

        // TODO: attach any processors defined in channelConfig['processors']
        // e.g. TraceContextProcessor, RedactionProcessor, ChecksumProcessor

        // Return the fully configured Logger
        return $logger;
    }
}
