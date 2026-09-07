<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Pesapal API v3 — hosted checkout covering cards, M-Pesa, Airtel Money.
 * Docs: https://developer.pesapal.com/
 */
class PesapalGateway implements PaymentGateway
{
    public function key(): string
    {
        return 'pesapal';
    }

    public function label(): string
    {
        return 'Card / Mobile Money (Pesapal)';
    }

    protected function config(string $key, mixed $default = null): mixed
    {
        return config("services.pesapal.$key", $default);
    }

    public function isConfigured(): bool
    {
        return filled($this->config('consumer_key')) && filled($this->config('consumer_secret'));
    }

    protected function baseUrl(): string
    {
        return $this->config('env') === 'production'
            ? 'https://pay.pesapal.com/v3'
            : 'https://cybqa.pesapal.com/pesapalv3';
    }

    protected function token(): string
    {
        return Cache::remember('pesapal.token', now()->addMinutes(4), function () {
            $res = Http::acceptJson()->post($this->baseUrl().'/api/Auth/RequestToken', [
                'consumer_key' => $this->config('consumer_key'),
                'consumer_secret' => $this->config('consumer_secret'),
            ]);

            if (! $res->json('token')) {
                throw new RuntimeException('Pesapal auth failed: '.$res->body());
            }

            return $res->json('token');
        });
    }

    /** Register (once) the IPN URL and cache its id. Run via `php artisan pesapal:register-ipn`. */
    public function registerIpn(): string
    {
        $res = Http::withToken($this->token())->acceptJson()->post($this->baseUrl().'/api/URLSetup/RegisterIPN', [
            'url' => route('webhooks.pesapal'),
            'ipn_notification_type' => 'GET',
        ]);

        $id = $res->json('ipn_id');
        if (! $id) {
            throw new RuntimeException('Pesapal IPN registration failed: '.$res->body());
        }
        Cache::forever('pesapal.ipn_id', $id);

        return $id;
    }

    protected function ipnId(): ?string
    {
        return $this->config('ipn_id') ?: Cache::get('pesapal.ipn_id');
    }

    public function initiate(Order $order, array $context = []): PaymentInitiation
    {
        $payment = $order->payments()->create([
            'gateway' => 'pesapal',
            'method' => 'card',
            'status' => 'initiated',
            'amount' => (int) ceil($order->grand_total),
        ]);

        $payload = [
            'id' => $order->number.'-'.$payment->id,
            'currency' => $order->currency,
            'amount' => (float) $order->grand_total,
            'description' => 'Payment for order '.$order->number,
            'callback_url' => route('checkout.return', $order),
            'notification_id' => $this->ipnId(),
            'billing_address' => [
                'email_address' => $order->customer_email,
                'phone_number' => $order->customer_phone,
                'first_name' => $order->customer_name,
            ],
        ];

        $res = Http::withToken($this->token())->acceptJson()
            ->post($this->baseUrl().'/api/Transactions/SubmitOrderRequest', $payload);

        $payment->update([
            'raw_request' => $payload,
            'raw_response' => $res->json(),
            'gateway_ref' => $res->json('order_tracking_id'),
            'status' => $res->json('redirect_url') ? 'pending' : 'failed',
        ]);

        if (! $res->json('redirect_url')) {
            Log::warning('Pesapal SubmitOrderRequest failed', ['order' => $order->number, 'body' => $res->body()]);

            return PaymentInitiation::poll($payment, 'Could not start card payment. Please try M-Pesa or contact us.');
        }

        $order->update(['status' => 'awaiting_payment', 'payment_status' => 'pending', 'payment_method' => 'pesapal']);

        return PaymentInitiation::redirect($payment, $res->json('redirect_url'));
    }

    public function handleCallback(Request $request): void
    {
        $trackingId = $request->query('OrderTrackingId') ?? $request->input('OrderTrackingId');
        if (! $trackingId) {
            return;
        }

        $payment = Payment::where('gateway_ref', $trackingId)->first();
        if ($payment) {
            $this->verify($payment);
        }
    }

    public function verify(Payment $payment): string
    {
        if (! $payment->gateway_ref) {
            return $payment->status;
        }

        $res = Http::withToken($this->token())->acceptJson()
            ->get($this->baseUrl().'/api/Transactions/GetTransactionStatus', [
                'orderTrackingId' => $payment->gateway_ref,
            ]);

        $statusCode = (int) $res->json('status_code'); // 1=completed, 2=failed, 3=reversed
        $desc = $res->json('payment_status_description');

        if ($statusCode === 1 && $payment->status !== 'success') {
            $payment->markSuccess($res->json('confirmation_code') ?: $payment->gateway_ref, ['status' => $res->json()]);
            $payment->order->markPaid('pesapal');
            OrderFulfilment::run($payment->order);
        } elseif (in_array($statusCode, [2, 3], true)) {
            $payment->markFailed(['status' => $res->json()]);
            $payment->order->update(['payment_status' => 'failed']);
        }

        Log::info('Pesapal verify', ['payment' => $payment->id, 'code' => $statusCode, 'desc' => $desc]);

        return $payment->fresh()->status;
    }
}
