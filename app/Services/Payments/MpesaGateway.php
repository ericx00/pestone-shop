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
 * Safaricom Daraja — Lipa na M-Pesa Online (STK push).
 * Docs: https://developer.safaricom.co.ke/
 */
class MpesaGateway implements PaymentGateway
{
    public function key(): string
    {
        return 'mpesa';
    }

    public function label(): string
    {
        return 'M-Pesa (STK push)';
    }

    protected function config(string $key, mixed $default = null): mixed
    {
        return config("services.mpesa.$key", $default);
    }

    public function isConfigured(): bool
    {
        return filled($this->config('consumer_key'))
            && filled($this->config('consumer_secret'))
            && filled($this->config('shortcode'))
            && filled($this->config('passkey'));
    }

    protected function baseUrl(): string
    {
        return $this->config('env') === 'production'
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';
    }

    protected function token(): string
    {
        return Cache::remember('mpesa.token', now()->addMinutes(50), function () {
            $res = Http::withBasicAuth($this->config('consumer_key'), $this->config('consumer_secret'))
                ->get($this->baseUrl().'/oauth/v1/generate', ['grant_type' => 'client_credentials']);

            if (! $res->successful() || ! $res->json('access_token')) {
                throw new RuntimeException('M-Pesa auth failed: '.$res->body());
            }

            return $res->json('access_token');
        });
    }

    public static function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '254'.substr($phone, 1);
        } elseif (str_starts_with($phone, '7') || str_starts_with($phone, '1')) {
            $phone = '254'.$phone;
        } elseif (str_starts_with($phone, '+')) {
            $phone = ltrim($phone, '+');
        }

        return $phone;
    }

    public function initiate(Order $order, array $context = []): PaymentInitiation
    {
        $phone = self::normalizePhone($context['phone'] ?? $order->customer_phone);
        $amount = max(1, (int) ceil($order->grand_total)); // M-Pesa uses whole shillings
        $timestamp = now()->format('YmdHis');
        $password = base64_encode($this->config('shortcode').$this->config('passkey').$timestamp);

        $payment = $order->payments()->create([
            'gateway' => 'mpesa',
            'method' => 'stk',
            'status' => 'initiated',
            'amount' => $amount,
            'phone' => $phone,
        ]);

        $payload = [
            'BusinessShortCode' => $this->config('shortcode'),
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => $this->config('transaction_type', 'CustomerPayBillOnline'),
            'Amount' => $amount,
            'PartyA' => $phone,
            'PartyB' => $this->config('shortcode'),
            'PhoneNumber' => $phone,
            'CallBackURL' => route('webhooks.mpesa'),
            'AccountReference' => $order->number,
            'TransactionDesc' => 'Payment for '.$order->number,
        ];

        $res = Http::withToken($this->token())
            ->post($this->baseUrl().'/mpesa/stkpush/v1/processrequest', $payload);

        $payment->update([
            'raw_request' => array_merge($payload, ['Password' => '***']),
            'raw_response' => $res->json(),
            'merchant_request_id' => $res->json('MerchantRequestID'),
            'checkout_request_id' => $res->json('CheckoutRequestID'),
            'status' => $res->json('ResponseCode') === '0' ? 'pending' : 'failed',
        ]);

        if ($res->json('ResponseCode') !== '0') {
            Log::warning('M-Pesa STK push rejected', ['order' => $order->number, 'body' => $res->body()]);

            return PaymentInitiation::poll($payment, $res->json('errorMessage') ?? $res->json('ResponseDescription') ?? 'M-Pesa request failed. Please try again.');
        }

        $order->update(['status' => 'awaiting_payment', 'payment_status' => 'pending', 'payment_method' => 'mpesa']);

        return PaymentInitiation::poll($payment, 'Check your phone and enter your M-Pesa PIN to complete payment.');
    }

    public function handleCallback(Request $request): void
    {
        $body = $request->json('Body.stkCallback');
        if (! $body) {
            Log::warning('M-Pesa callback with no stkCallback body', $request->all());

            return;
        }

        $checkoutId = $body['CheckoutRequestID'] ?? null;
        $payment = Payment::where('checkout_request_id', $checkoutId)->first();

        if (! $payment) {
            Log::warning('M-Pesa callback for unknown CheckoutRequestID', ['id' => $checkoutId]);

            return;
        }

        if ($payment->status === 'success') {
            return; // idempotent
        }

        $resultCode = (int) ($body['ResultCode'] ?? -1);
        $meta = collect($body['CallbackMetadata']['Item'] ?? [])->keyBy('Name');

        if ($resultCode === 0) {
            $paidAmount = (int) ($meta['Amount']['Value'] ?? 0);
            if ($paidAmount > 0 && $paidAmount < $payment->amount) {
                Log::error('M-Pesa amount mismatch', ['payment' => $payment->id, 'expected' => $payment->amount, 'paid' => $paidAmount]);
                $payment->markFailed(['reason' => 'amount_mismatch', 'callback' => $body]);

                return;
            }

            $payment->markSuccess((string) ($meta['MpesaReceiptNumber']['Value'] ?? ''), ['callback' => $body]);
            $payment->order->markPaid('mpesa');
            OrderFulfilment::run($payment->order);
        } else {
            $payment->markFailed(['result_desc' => $body['ResultDesc'] ?? '', 'callback' => $body]);
            $payment->order->update(['payment_status' => 'failed']);
        }
    }

    public function verify(Payment $payment): string
    {
        if (! $payment->checkout_request_id) {
            return $payment->status;
        }

        $timestamp = now()->format('YmdHis');
        $password = base64_encode($this->config('shortcode').$this->config('passkey').$timestamp);

        $res = Http::withToken($this->token())->post($this->baseUrl().'/mpesa/stkpushquery/v1/query', [
            'BusinessShortCode' => $this->config('shortcode'),
            'Password' => $password,
            'Timestamp' => $timestamp,
            'CheckoutRequestID' => $payment->checkout_request_id,
        ]);

        $code = $res->json('ResultCode');

        if ($code === '0' && $payment->status !== 'success') {
            $payment->markSuccess($payment->gateway_ref, ['query' => $res->json()]);
            $payment->order->markPaid('mpesa');
            OrderFulfilment::run($payment->order);
        } elseif (in_array($code, ['1032', '1', '1037', '2001'], true)) {
            $payment->markFailed(['query' => $res->json()]);
        }

        return $payment->fresh()->status;
    }
}
