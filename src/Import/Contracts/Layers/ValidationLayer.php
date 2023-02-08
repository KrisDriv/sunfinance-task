<?php
declare(strict_types=1);

namespace App\Import\Contracts\Layers;

use App\Import\Contracts\ImportLayer;
use App\Import\Contracts\ValidatesFields;
use App\Import\Traits\DynamicFieldCalls;

class ValidationLayer extends ImportLayer implements ValidatesFields
{
    use DynamicFieldCalls {
        validateField as public;
        transformField as private;
    }
}