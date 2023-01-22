<?php
declare(strict_types=1);

if (!function_exists('get_reflection_aligned_type')) {

    /**
     * ReflectionType names do not align with those that are returned from @see gettype function
     *
     * @param mixed $variable
     * @return string
     */
    function get_reflection_aligned_type(mixed $variable): string
    {
        return match ($originalType = gettype($variable)) {
            'boolean' => 'bool',
            'integer' => 'int',
            'double' => 'float',
            default => $originalType
        };
    }

}