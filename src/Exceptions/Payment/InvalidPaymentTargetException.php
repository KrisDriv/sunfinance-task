<?php
declare(strict_types=1);

namespace App\Exceptions\Payment;

use App\Entities\PaymentEntity;
use Throwable;

class InvalidPaymentTargetException extends PaymentException
{

}