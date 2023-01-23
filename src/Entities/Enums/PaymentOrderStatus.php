<?php

declare(strict_types=1);

namespace App\Entities\Enums;

enum PaymentOrderStatus
{
	case PAID;
	case PENDING;
	case CANCELLED;
}
