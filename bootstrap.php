<?php
declare(strict_types=1);

use Composite\DB\ConnectionManager;
use Dotenv\Dotenv;

const ROOT = __DIR__ . DIRECTORY_SEPARATOR;
const APP = ROOT . 'src' . DIRECTORY_SEPARATOR;

if (file_exists(ROOT . 'vendor/autoload.php')) {
    require ROOT . 'vendor/autoload.php';
}

foreach (glob(ROOT . 'helpers/*.php') as $helperFile) {
    require $helperFile;
}

$dotenv = Dotenv::createUnsafeImmutable(ROOT);
$dotenv->load();

define('ENVIRONMENT', strtolower($_ENV['ENVIRONMENT']));
define('IS_DEV', in_array(ENVIRONMENT, ['dev', 'development']));
define('IS_TEST', in_array(ENVIRONMENT, ['test', 'testing']));
define('IS_PRODUCTION', in_array(ENVIRONMENT, ['prod', 'production']));

/**
 * Load .env file specifically for that environment.
 *
 * Will overwrite previously set values in .env
 */
$dotenv = Dotenv::createUnsafeImmutable(ROOT, ".env." . ENVIRONMENT);

/**
 * Initiate ORM
 */
$databaseConnection = ConnectionManager::getConnection($_ENV['DATABASE_CONNECTION']);