<?php
declare(strict_types=1);

namespace App\Import\Contracts\Layers;

use App\Import\Contracts\ImportLayer;
use App\Import\Contracts\TransformsFields;
use App\Import\Traits\DynamicFieldCalls;

class TransformLayer extends ImportLayer implements TransformsFields
{
    use DynamicFieldCalls {
        validateField as private;
        transformField as public;
    }
}