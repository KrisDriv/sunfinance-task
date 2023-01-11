<?php
declare(strict_types=1);

if (!function_exists('database_path')) {

    function database_path(string $file): string
    {
        return ROOT . 'database' . DIRECTORY_SEPARATOR . $file;
    }

}