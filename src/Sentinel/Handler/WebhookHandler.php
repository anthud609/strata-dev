<?php
// src/Sentinel/Handler/WebhookHandler.php
namespace Sentinel\Handler;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;
use GuzzleHttp\Client;

class WebhookHandler extends AbstractProcessingHandler
{
    private Client $http;
    private string $url;

    public function __construct(string $url, string $method = 'POST')
    {
        parent::__construct();
        $this->http = new Client();
        $this->url  = $url;
    }

    protected function write(LogRecord $record): void
    {
        $this->http->request('POST', $this->url, [
            'json' => $record->toArray(),
        ]);
    }
}
