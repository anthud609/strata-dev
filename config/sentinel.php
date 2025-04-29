<?php
/**
 * config/sentinel.php
 *
 * Configuration for Sentinel’s logging, alerting, tracing, and immutable storage.
 */

return [

    //------------------------------------------------------------------------------
    // Patterns your RedactionProcessor will apply to the 'context'
    //------------------------------------------------------------------------------
    'redaction_patterns' => [
        '/"password"\s*:\s*".*?"/i',
        '/\b\d{12,16}\b/',
        // …add additional PII/PHI regexes here
    ],

    //------------------------------------------------------------------------------
    // 1) Monolog channel definitions
    //------------------------------------------------------------------------------
    // Define one or more PSR-3 channels, each with its own handlers & processors.
    // 1) Channel definitions
    'channels' => [
        'default' => [
            'handlers' => [
                [
                    'type'   => 'stream',
                    'path'   => __DIR__ . '/../storage/logs/app.log',
                    'level'  => 'DEBUG',
                    'format' => "[%datetime%] %channel%.%level_name%: %message% %context% error_id=%extra.error_id%\n",
                ],
                // … other handlers …
            ],
            'processors' => [
                // Inject a unique error_id per record
                ['class' => \Sentinel\Processor\ErrorIdProcessor::class],

                // Still keep redaction if you want
                ['class' => \Sentinel\Processor\RedactionProcessor::class],
            ],
        ],
    ],


    //------------------------------------------------------------------------------
    // 2) Alerting engine configuration
    //------------------------------------------------------------------------------
    // Define rules that trigger notifications when conditions occur.
    'alerting' => [

        'rules' => [
            [
                'name'      => 'high_error_rate',    // Unique rule identifier
                'channel'   => 'default',            // Which log channel to monitor
                'type'      => 'threshold',          // 'threshold' | 'anomaly' | 'baseline'
                'metric'    => 'error_count',        // Event or metric name
                'threshold' => 100,                  // Numeric threshold
                'window'    => '1m',                 // Evaluation window ('1m', '5m', etc.)
                'route'     => 'slack',              // Key in the 'webhooks' section
            ],

            // …add additional alert rules here
        ],

        // Suppress duplicate alerts for the same rule within this timeframe
        'deduplication_window' => '15m',
    ],


    //------------------------------------------------------------------------------
    // 3) Webhook/notification destinations
    //------------------------------------------------------------------------------
    // Where to send alerts once rules fire.
    'webhooks' => [

        'slack' => [
            'url'      => env('SLACK_WEBHOOK_URL'),  // Load from .env or vault
            'channel'  => '#alerts',
            'username' => 'SentinelBot',
        ],

        'pagerduty' => [
            'service_key' => env('PAGERDUTY_SERVICE_KEY'),
        ],

        'email' => [
            'recipients' => ['ops@example.com', 'dev@example.com'],
            'subject'    => 'Sentinel Alert: {{rule_name}}',
        ],

        // Define other webhooks (Teams, ServiceNow, etc.) here…
    ],


    //------------------------------------------------------------------------------
    // 4) Immutable WORM-style storage settings
    //------------------------------------------------------------------------------
    // Ensures logs are write-once, read-many with tamper-evident checksums.
    'worm' => [
        'storage_path'   => __DIR__ . '/../storage/sentinel/worm',
        'checksum_table' => 'sentinel_checksums',
        'algorithm'      => 'sha256',
        'verify_interval'=> '60m',      // How often to verify the hash chain
    ],


    //------------------------------------------------------------------------------
    // 5) Distributed tracing headers
    //------------------------------------------------------------------------------
    // Header names used for W3C Trace Context propagation.
    'correlation' => [
        'trace_header' => 'traceparent',
        'state_header' => 'tracestate',
    ],


    //------------------------------------------------------------------------------
    // 6) Database migration table names
    //------------------------------------------------------------------------------
    // Names of the tables created by your migration scripts.
    'migrations' => [
        'alerts_table'    => 'sentinel_alerts',
        'checksums_table' => 'sentinel_checksums',
    ],

];
