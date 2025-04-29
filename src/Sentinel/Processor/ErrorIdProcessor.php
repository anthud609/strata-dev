<?php
// src/Sentinel/Processor/ErrorIdProcessor.php

namespace Sentinel\Processor;

use Monolog\Processor\ProcessorInterface;
use Monolog\LogRecord;
use Ramsey\Uuid\Uuid;

class ErrorIdProcessor implements ProcessorInterface
{
    public function __invoke(LogRecord $record): LogRecord
    {
        // Generate a v4 UUID for this log record
        $errorId = Uuid::uuid4()->toString();

        // Inject into 'extra'
        $extra = $record->extra;
        $extra['error_id'] = $errorId;

        return $record->with(extra: $extra);
    }
}
