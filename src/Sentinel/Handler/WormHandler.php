<?php
// src/Sentinel/Handler/WormHandler.php
namespace Sentinel\Handler;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use PDO;

class WormHandler extends AbstractProcessingHandler
{
    private string $storagePath;
    private PDO    $pdo;
    private string $checksumTable;
    private string $algo;

    public function __construct(string $storagePath, string $checksumTable, string $algo)
    {
        parent::__construct(); 
        $this->storagePath   = $storagePath;
        $this->checksumTable = $checksumTable;
        $this->algo          = $algo;
        // initialize your PDO connection here…
    }

    protected function write(LogRecord $record): void
    {
        $line = $this->formatRecord($record);
        file_put_contents($this->storagePath . '/'. uniqid() .'.log', $line);
        // compute and store rolling checksum in DB…
    }

    private function formatRecord(LogRecord $r): string
    {
        return json_encode($r->toArray()) . "\n";
    }
}
