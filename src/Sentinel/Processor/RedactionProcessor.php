<?php
// src/Sentinel/Processor/RedactionProcessor.php

namespace Sentinel\Processor;

use Monolog\Processor\ProcessorInterface;
use Monolog\LogRecord;

class RedactionProcessor implements ProcessorInterface
{
    /** @var string[] */
    private array $patterns;

    public function __construct(array $config)
    {
        // pull from config (e.g. top-level 'redaction_patterns')
        $this->patterns = $config['redaction_patterns'] ?? [];
    }

    public function __invoke(LogRecord $record): LogRecord
    {
        $contextJson = json_encode($record->context);

        foreach ($this->patterns as $regex) {
            $contextJson = preg_replace($regex, '"[REDACTED]"', $contextJson);
        }

        $context = json_decode($contextJson, true) ?: $record->context;

        // replace context on the record
        return $record->with(context: $context);
    }
}
