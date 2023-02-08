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

if (!function_exists('is_accessible')) {

    /**
     * Test if visibility permits a method call. Extension to is_callable. Function is deliberately placed
     * in global scope for this function to return consistent and accurate results. Otherwise, is_callable
     * call from within the referenced object will return true due to respect of its own scope.
     *
     * @param callable $callable
     * @return bool
     */
    function is_accessible(callable $callable): bool
    {
        return is_callable($callable);
    }

}