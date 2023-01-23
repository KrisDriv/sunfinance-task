<?php
declare(strict_types=1);

if (!function_exists('database_path')) {

    function database_path(string $file): string
    {
        return ROOT . 'database' . DIRECTORY_SEPARATOR . $file;
    }

}

if (!function_exists('base_path')) {

    function base_path(string $pathToFile): string
    {
        return ROOT . $pathToFile;
    }

}

if (!function_exists('tmp_path')) {

    function tmp_path(string $pathToFile): string
    {
        return TMP . $pathToFile;
    }

}