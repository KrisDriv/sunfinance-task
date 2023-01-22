<?php

declare(strict_types=1);

namespace App\Entities\Enums;

enum LoanState
{
	case ACTIVE;
	case PAID;
}
