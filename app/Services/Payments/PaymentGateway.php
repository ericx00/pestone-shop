<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;

interface PaymentGateway
{
    /** Machine key, e.g. "mpesa". */
    public function key(): string;

    /** Human label for checkout UI. */
    public function label(): string;

    public function isConfigured(): bool;

    /**
     * Start a payment for an order.
     *
     * @param  array<string,mixed>  $context  e.g. ['phone' => '2547...']
     * @return PaymentInitiation
     */
    public function initiate(Order $order, array $context = []): PaymentInitiation;

    /** Handle an asynchronous callback / IPN from the gateway. */
    public function handleCallback(Request $request): void;

    /** Actively verify a payment's status with the gateway (source of truth). */
    public function verify(Payment $payment): string;
}
