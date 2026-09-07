<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Services\Cart\CartService;
use App\Services\Payments\MpesaGateway;
use App\Services\Payments\PaymentManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function __construct(
        protected CartService $cart,
        protected PaymentManager $payments,
    ) {}

    public function index(Request $request)
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $zone = $request->input('zone', 'nairobi_cbd');

        return view('checkout.index', [
            'summary' => $this->cart->summary($zone),
            'zone' => $zone,
            'gateways' => $this->payments->available(),
            'user' => Auth::user(),
        ]);
    }

    public function place(Request $request)
    {
        if ($this->cart->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }

        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_email' => ['nullable', 'email', 'max:160'],
            'customer_phone' => ['required', 'string', 'max:20'],
            'customer_company' => ['nullable', 'string', 'max:160'],
            'line1' => ['required', 'string', 'max:160'],
            'town' => ['required', 'string', 'max:80'],
            'county' => ['nullable', 'string', 'max:80'],
            'delivery_zone' => ['required', 'in:nairobi_cbd,nairobi_metro,countrywide,pickup'],
            'payment_method' => ['required', 'string'],
            'mpesa_phone' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $summary = $this->cart->summary($data['delivery_zone']);
        $user = Auth::user();

        $order = DB::transaction(function () use ($data, $summary, $user) {
            $order = Order::create([
                'number' => Order::generateNumber(),
                'user_id' => $user?->id,
                'channel' => $user?->isApprovedB2B() ? 'b2b' : 'b2c',
                'status' => 'pending',
                'payment_status' => 'unpaid',
                'payment_method' => $data['payment_method'],
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'] ?? $user?->email,
                'customer_phone' => $data['customer_phone'],
                'customer_company' => $data['customer_company'] ?? null,
                'shipping_address' => [
                    'line1' => $data['line1'],
                    'town' => $data['town'],
                    'county' => $data['county'] ?? null,
                ],
                'delivery_zone' => $data['delivery_zone'],
                'subtotal' => $summary['subtotal'],
                'discount_total' => $summary['discount'],
                'vat_total' => $summary['vat'],
                'shipping_total' => $summary['shipping'],
                'grand_total' => $summary['total'],
                'notes' => $data['notes'] ?? null,
                'placed_at' => now(),
            ]);

            foreach ($summary['lines'] as $line) {
                /** @var Product $product */
                $product = $line['product'];
                $order->items()->create([
                    'product_id' => $product->id,
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'unit_price' => $line['unit_net'],
                    'cost_price_snapshot' => $product->cost_price,
                    'qty' => $line['qty'],
                    'line_total' => $line['line_net'],
                ]);
            }

            return $order;
        });

        $this->cart->clear();

        return redirect()->route('checkout.pay.form', $order);
    }

    /** Show the "pay now" screen for an unpaid order. */
    public function payForm(Order $order)
    {
        abort_if($order->isPaid(), 302, '', ['Location' => route('checkout.return', $order)]);

        return view('checkout.pay', [
            'order' => $order->load('items'),
            'gateways' => $this->payments->available(),
        ]);
    }

    public function pay(Request $request, Order $order)
    {
        if ($order->isPaid()) {
            return redirect()->route('checkout.return', $order);
        }

        $data = $request->validate([
            'payment_method' => ['required', 'string'],
            'mpesa_phone' => ['nullable', 'string', 'max:20'],
        ]);

        $gateway = $this->payments->gateway($data['payment_method']);
        $context = [];
        if ($data['payment_method'] === 'mpesa') {
            $context['phone'] = MpesaGateway::normalizePhone($data['mpesa_phone'] ?: $order->customer_phone);
        }

        $init = $gateway->initiate($order, $context);

        if ($init->mode === 'redirect' && $init->redirectUrl) {
            return redirect()->away($init->redirectUrl);
        }

        return view('checkout.processing', [
            'order' => $order,
            'payment' => $init->payment,
            'message' => $init->message,
        ]);
    }

    /** JSON polled by the processing screen. */
    public function status(Order $order)
    {
        $payment = $order->payments()->latest()->first();

        if ($payment && ! in_array($payment->status, ['success', 'failed'], true)) {
            try {
                $this->payments->gateway($payment->gateway)->verify($payment);
                $payment->refresh();
            } catch (\Throwable $e) {
                // leave as pending; the callback is the primary path
            }
        }

        $order->refresh();

        return response()->json([
            'order_status' => $order->status,
            'payment_status' => $order->payment_status,
            'paid' => $order->isPaid(),
            'gateway_status' => $payment?->status,
            'redirect' => $order->isPaid() ? route('checkout.return', $order) : null,
        ]);
    }

    public function return(Order $order)
    {
        $payment = $order->payments()->latest()->first();
        if ($payment && ! $order->isPaid() && ! in_array($payment->status, ['failed'], true)) {
            try {
                $this->payments->gateway($payment->gateway)->verify($payment);
                $order->refresh();
            } catch (\Throwable $e) {
            }
        }

        return view('checkout.return', ['order' => $order->load('items', 'payments')]);
    }
}
