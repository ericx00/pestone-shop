<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MpesaCallbackTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SettingsSeeder::class);
    }

    protected function orderWithPayment(int $amount = 1000): array
    {
        $product = Product::create([
            'name' => 'Item', 'slug' => 'item', 'cost_price' => 500, 'price' => 1160,
            'stock_status' => 'in_stock', 'stock_qty' => 5,
        ]);
        $order = Order::create([
            'number' => Order::generateNumber(), 'customer_name' => 'A', 'customer_phone' => '254712345678',
            'status' => 'awaiting_payment', 'payment_status' => 'pending', 'grand_total' => $amount,
            'subtotal' => $amount, 'vat_total' => 0, 'shipping_total' => 0,
        ]);
        $order->items()->create([
            'product_id' => $product->id, 'name' => 'Item', 'unit_price' => $amount,
            'cost_price_snapshot' => 500, 'qty' => 1, 'line_total' => $amount,
        ]);
        $payment = $order->payments()->create([
            'gateway' => 'mpesa', 'method' => 'stk', 'status' => 'pending', 'amount' => $amount,
            'checkout_request_id' => 'ws_CO_TEST_1',
        ]);

        return [$order, $payment, $product];
    }

    protected function callbackPayload(string $checkoutId, int $resultCode, int $amount): array
    {
        return ['Body' => ['stkCallback' => [
            'MerchantRequestID' => 'm-1',
            'CheckoutRequestID' => $checkoutId,
            'ResultCode' => $resultCode,
            'ResultDesc' => $resultCode === 0 ? 'The service request is processed successfully.' : 'Cancelled by user',
            'CallbackMetadata' => ['Item' => [
                ['Name' => 'Amount', 'Value' => $amount],
                ['Name' => 'MpesaReceiptNumber', 'Value' => 'ABC123'],
                ['Name' => 'PhoneNumber', 'Value' => 254712345678],
            ]],
        ]]];
    }

    public function test_successful_callback_marks_order_paid_and_decrements_stock(): void
    {
        [$order, $payment, $product] = $this->orderWithPayment(1000);

        $this->postJson('/webhooks/mpesa', $this->callbackPayload('ws_CO_TEST_1', 0, 1000))
            ->assertOk()->assertJson(['ResultCode' => 0]);

        $this->assertSame('paid', $order->fresh()->payment_status);
        $this->assertSame('success', $payment->fresh()->status);
        $this->assertSame('ABC123', $payment->fresh()->gateway_ref);
        $this->assertSame(4, $product->fresh()->stock_qty);
    }

    public function test_callback_is_idempotent(): void
    {
        [$order, $payment, $product] = $this->orderWithPayment(1000);
        $payload = $this->callbackPayload('ws_CO_TEST_1', 0, 1000);

        $this->postJson('/webhooks/mpesa', $payload)->assertOk();
        $this->postJson('/webhooks/mpesa', $payload)->assertOk();

        $this->assertSame(4, $product->fresh()->stock_qty); // not decremented twice
    }

    public function test_amount_mismatch_is_rejected(): void
    {
        [$order, $payment] = $this->orderWithPayment(1000);

        $this->postJson('/webhooks/mpesa', $this->callbackPayload('ws_CO_TEST_1', 0, 500))->assertOk();

        $this->assertSame('failed', $payment->fresh()->status);
        $this->assertNotSame('paid', $order->fresh()->payment_status);
    }

    public function test_cancelled_payment_marks_failed(): void
    {
        [$order, $payment] = $this->orderWithPayment(1000);

        $this->postJson('/webhooks/mpesa', $this->callbackPayload('ws_CO_TEST_1', 1032, 0))->assertOk();

        $this->assertSame('failed', $payment->fresh()->status);
    }
}
