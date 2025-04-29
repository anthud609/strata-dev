<?php
/**
 * bootstrap/sentinel.php
 *
 * Bootstraps Sentinel by loading its configuration, creating the PSR-3 Logger,
 * and registering it with the static Facade for global access.
 *
 * @throws \RuntimeException if the configuration file cannot be loaded or is invalid.
 */

use Sentinel\Factory;
use Sentinel\Facade;

//------------------------------------------------------------------------------
// 1) Load Sentinel configuration
//------------------------------------------------------------------------------
// The config file returns an array defining channels, handlers, processors,
// alert rules, WORM settings, and any other runtime options.
$config = require __DIR__ . '/../config/sentinel.php';

//------------------------------------------------------------------------------
// 2) Build the PSR-3 Logger instance
//------------------------------------------------------------------------------
// Factory::create wires up Monolog channels, handlers, formatters, and
// any custom processors (PII redaction, Trace Context propagation, etc.).
$sentinel = Factory::create($config);

//------------------------------------------------------------------------------
// 3) Register with the static Facade
//------------------------------------------------------------------------------
// Facade::setInstance makes this logger available via Sentinel\Facade::{level}()
// so the rest of your code can log without passing around the logger object.
Facade::setInstance($sentinel);
