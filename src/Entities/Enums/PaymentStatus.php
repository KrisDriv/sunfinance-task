<?php

declare(strict_types=1);

namespace App\Entities\Enums;

enum PaymentStatus
{
	case ASSIGNED;
	case PARTIALLY_ASSIGNED;
}
