<?php

namespace App\Services\Payments;

use App\Models\Payment;

class PaymentInitiation
{
    public function __construct(
        public Payment $payment,
        /** "poll" (STK — client polls status) or "redirect" (hosted checkout). */
        public string $mode = 'poll',
        public ?string $redirectUrl = null,
        public ?string $message = null,
    ) {}

    public static function poll(Payment $payment, ?string $message = null): self
    {
        return new self($payment, 'poll', null, $message);
    }

    public static function redirect(Payment $payment, string $url): self
    {
        return new self($payment, 'redirect', $url);
    }
}
