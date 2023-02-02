<?php

namespace App\Listeners;

use App\Events\Loan\LoanPaidEvent;
use App\Events\Payment\PaymentFailedEvent;
use App\Events\Payment\PaymentReceivedEvent;
use App\Listeners\Concrete\Listener;

class PaymentsListener extends Listener
{

    protected array $handlers = [
        PaymentFailedEvent::NAME => 'failedPaymentHandler',
        LoanPaidEvent::NAME => 'loanPaidHandler',
        PaymentReceivedEvent::NAME => 'receivedPaymentHandler'
    ];

    public function failedPaymentHandler(PaymentFailedEvent $event): void
    {
        // TODO: Do whatever, send notifications etc.
    }

    public function loanPaidHandler(LoanPaidEvent $event): void
    {
        // TODO: Do whatever, send notifications etc.
    }

    public function receivedPaymentHandler(PaymentReceivedEvent $event): void
    {
        // TODO: Do whatever, send notifications etc.
    }

}