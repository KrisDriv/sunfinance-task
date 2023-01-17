<?php

/**
 * Composer autoload.
 */
(function (string $composerAutoloadFilepath): void {

    if (file_exists($composerAutoloadFilepath)) {
        require_once $composerAutoloadFilepath;
    } else {
        if (php_sapi_name() === 'cli') {
            die("Could not find composer autoload file. Make sure to execute 'composer install'");
        } else {
            header('status: 500');
        }
    }

})(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor/autoload.php');