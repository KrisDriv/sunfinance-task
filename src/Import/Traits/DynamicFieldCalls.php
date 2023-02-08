<?php
declare(strict_types=1);

namespace App\Import\Traits;

use App\Tables\AbstractTable;
use BadMethodCallException;
use function Symfony\Component\String\s;

/**
 * Redirects calls for field callbacks so that each field can be handled by single method
 */
trait DynamicFieldCalls
{

    private static int $FIELD_ARGUMENT_INDEX = 0;

    public function transformField(string $field, mixed $raw, array $dataset, AbstractTable $table): mixed
    {
        return $this->redirectFieldCallback('transform', $field, [$raw, $dataset, $table]);
    }

    public function validateField(string $field, mixed $raw, array $dataset, AbstractTable $table): mixed
    {
        return $this->redirectFieldCallback('validate', $field, [$raw, $dataset, $table]);
    }

    private function redirectFieldCallback(string $operation, string $field, array $arguments)
    {
        // [operation: validateTransform] + [PascalFormatField]
        // eg. $this->validateField('last_name', ...) redirects to -> $this->>validateLastName()
        $targetMethod = $operation . s($field)->camel()->title();

        // Silently ignore missing methods, they are not required but will be called and used if method does exist.
        if (!is_accessible([$this, $targetMethod])) {
            throw new BadMethodCallException(sprintf(
                "%s call redirects to inaccessible or undefined method '%s'",
                $operation, $targetMethod
            ));
        }

        return call_user_func_array([$this, $targetMethod], array_slice($arguments, 1));
    }

}