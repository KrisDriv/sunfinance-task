<?php
declare(strict_types=1);

use JetBrains\PhpStorm\NoReturn;

if (!function_exists('dd')) {

    /**
     * Dumps and Dies
     *
     * @param array $vars
     */
    #[NoReturn]
    function dd(...$vars): void
    {
        echo '<pre>';
        array_map(
            function (mixed $var): void {
                echo print_r($var, true);
            },
            $vars
        );
        echo '</pre>';

        exit(0);
    }
}
