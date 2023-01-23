<?php

namespace App\Listeners;

use App\Events\FailedPaymentEvent;
use App\Events\LoanPaidEvent;
use App\Events\PaymentReceivedEvent;
use App\Listeners\Concrete\Listener;

class PaymentsListener extends Listener
{

    protected array $handlers = [
        FailedPaymentEvent::NAME => 'failedPaymentHandler',
        LoanPaidEvent::NAME => 'loanPaidHandler',
        PaymentReceivedEvent::NAME => 'receivedPaymentHandler'
    ];

    public function failedPaymentHandler(FailedPaymentEvent $event): void
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