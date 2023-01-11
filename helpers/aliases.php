<?php
declare(strict_types=1);

if (!function_exists('env')) {

    /**
     * Alias for getenv function
     *
     * @param string $key
     * @return bool|array|string
     */
    function env(string $key): bool|array|string
    {
        return getenv($key);
    }
}