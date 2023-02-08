<?php

namespace App\Import\Enums;

enum ImportEvent: string
{
    case PRE_SAVE = 'pre-save';
    case PRE_HYDRATE = 'pre-hydrate';
    case ON_DUPLICATE = 'on-duplicate';
}
