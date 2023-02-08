<?php
declare(strict_types=1);

namespace App\Import\Contracts;

use App\Tables\AbstractTable;

/**
 * Use {@see \App\Import\Traits\DynamicFieldCalls} to redirect these calls to single separate methods
 */
interface ValidatesFields
{

    /**
     * Handles Validation calls
     *
     * @param string $field Field key
     * @param mixed $raw Raw input data
     * @param array $dataset Original dataset
     * @param AbstractTable $table
     * @return mixed
     */
    public function validateField(string $field, mixed $raw, array $dataset, AbstractTable $table): mixed;

}