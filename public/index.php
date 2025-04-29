<?php
/**
 * public/index.php
 *
 * Front controller for the application.  
 * Loads Composer’s autoloader, bootstraps core services and Sentinel,
 * emits an initial log entry, and then handles the HTTP response.
 *
 * @package YourApp
 */

declare(strict_types=1);

//------------------------------------------------------------------------------
// 1) Composer autoload – must come first
//------------------------------------------------------------------------------
// Registers all Composer dependencies and PSR-4 autoload mappings.
// Without this, none of your classes (including Sentinel) can be found.
require __DIR__ . '/../vendor/autoload.php';

//------------------------------------------------------------------------------
// 2) App bootstrap (env, DI, routes…)
//------------------------------------------------------------------------------
// Initialize environment variables, dependency injection container,
// middleware, and your application’s routing or CLI command registry.
require __DIR__ . '/../bootstrap/app.php';

//------------------------------------------------------------------------------
// 3) Sentinel bootstrap (config + set the Facade instance)
//------------------------------------------------------------------------------
// Load the Sentinel configuration, build the PSR-3 Logger via Factory,
// and register it on the static Facade for global logging access.
require __DIR__ . '/../bootstrap/sentinel.php';

//------------------------------------------------------------------------------
// 4) Initial log entry to verify Sentinel is operational
//------------------------------------------------------------------------------
// Emits an INFO-level message into your configured log targets.
// This confirms that Sentinel is up and capturing logs.
Sentinel\Facade::info('🚀 Sentinel is up!', ['foo' => 'bar']);

//------------------------------------------------------------------------------
// 5) Dispatch request / output response
//------------------------------------------------------------------------------
// Hand off control to your router or framework. For now, echo a confirmation.
echo '✅ Hello from your-app!';
