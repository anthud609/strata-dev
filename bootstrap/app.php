<?php
/**
 * bootstrap/app.php
 *
 * Application bootstrap for an enterprise-grade setup.  This file is responsible for
 * initializing all core services, configuration, and infrastructure before your
 * app handles any requests or CLI commands.
 *
 * @todo implement the following steps in your enterprise environment:
 *   1) Load environment variables (e.g. via phpdotenv) and configuration files
 *   2) Set error reporting levels and display settings based on APP_ENV
 *   3) Set the default timezone from configuration
 *   4) Initialize a DI/IoC container and register service providers:
 *      • Database (ORM or query builder)
 *      • Cache (Redis, Memcached)
 *      • Session management
 *      • Queue clients (RabbitMQ, SQS, etc.)
 *      • Event dispatcher / pub-sub
 *   5) Configure logging bridges (so app-level logs flow into Sentinel)
 *   6) Initialize and register global middleware:
 *      • CORS
 *      • CSRF protection
 *      • Body parsers (JSON, form data)
 *      • Authentication/authorization
 *   7) Bootstrap the HTTP router and load all application route definitions
 *   8) Register console commands and schedule recurring tasks
 *   9) Register health-check and metrics endpoints (for load balancers, monitoring)
 *  10) Integrate a global exception handler that funnels errors into Sentinel
 *  11) Initialize tracing/metrics providers for end-to-end observability
 *  12) Perform any startup migrations or cache warm-ups if needed
 *  13) Hook in any other infrastructure (feature flags, AB-testing, third-party SDKs)
 *
 * Once these steps are in place, your application will have a consistent, maintainable,
 * and testable foundation, fully wired into Sentinel for observability and alerting.
 */

// 1) Composer autoload
require __DIR__ . '/../vendor/autoload.php';

// 2) Load .env into $_ENV / $_SERVER
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
}

// 3) Define a global env() helper
if (! function_exists('env')) {
    /**
     * Get an environment variable or return default.
     *
     * @param string     $key
     * @param mixed|null $default
     * @return string|false|null
     */
    function env(string $key, $default = null)
    {
        $val = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        return $val === false || $val === null ? $default : $val;
    }
}
