<?php
declare(strict_types=1);

use App\Application;
use Dotenv\Dotenv;

define('ROOT', dirname(__DIR__) . DIRECTORY_SEPARATOR);
define('IS_CONSOLE', php_sapi_name() == 'cli');

/**
 * Load helper functions
 */
foreach (glob(ROOT . 'helpers/*.php') as $helperFile) {
    require $helperFile;
}

/**
 * Read environment files
 */
$dotenv = Dotenv::createUnsafeImmutable(ROOT);
$dotenv->load();

define('ENVIRONMENT', match (strtolower(env('ENVIRONMENT'))) {
    'dev', 'development' => 'development',
    'test', 'testing' => 'testing',
    'prod', 'production' => 'production',
    'stage', 'staging' => 'staging'
});

const IS_DEV = ENVIRONMENT === 'development';
const IS_TEST = ENVIRONMENT === 'testing';
const IS_PRODUCTION = ENVIRONMENT === 'production';
const IS_STAGING = ENVIRONMENT === 'staging';

define("TMP", getenv()['TMP_DIR'] ?? ROOT . 'tmp' . DIRECTORY_SEPARATOR);

/**
 * Load .env file specifically for that environment.
 *
 * Will overwrite previously set values in .env
 */
$dotenv = Dotenv::createUnsafeImmutable(ROOT, ".env." . ENVIRONMENT);
$dotenv->safeLoad();

$app = new Application();

/**
 * Register routes
 */
$router = $app->getRouter();

$router->setNamespace('App\\Controllers');

(require ROOT . 'routes/web.php')($router);

$router->mount('/api', function () use ($router) {
    (require ROOT . 'routes/api.php')($router);
});

/**
 * Clean up the global namespace.
 */
unset($dotenv);
unset($router);

return $app;