<?php
declare(strict_types=1);

use App\Application;
use Dotenv\Dotenv;

const ROOT = __DIR__ . DIRECTORY_SEPARATOR;

define('IS_CONSOLE', php_sapi_name() == 'cli');

/**
 * Composer autoload
 */
if (file_exists(ROOT . 'vendor/autoload.php')) {
    require ROOT . 'vendor/autoload.php';
} else {
    if (IS_CONSOLE) {
        die("Could not find composer autoload file. Make sure to execute 'composer install'");
    } else {
        header('status: 501');
    }
}

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

define('ENVIRONMENT', strtolower(env('ENVIRONMENT')));
define('IS_DEV', in_array(ENVIRONMENT, ['dev', 'development']));
define('IS_TEST', in_array(ENVIRONMENT, ['test', 'testing']));
define('IS_PRODUCTION', in_array(ENVIRONMENT, ['prod', 'production']));
define('IS_STAGING', in_array(ENVIRONMENT, ['stage', 'staging']));

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

require ROOT . 'routes/web.php';

$router->mount('/api', function () use ($router) {
    require ROOT . 'routes/api.php';
});

/**
 * Clean up the global namespace.
 */
unset($dotenv);
unset($router);

return $app;