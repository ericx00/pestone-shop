<?php

namespace App\Services\Payments;

use App\Models\InventoryMovement;
use App\Models\Order;
use App\Notifications\OrderConfirmed;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class OrderFulfilment
{
    /** Called exactly once when an order becomes paid: decrement stock, notify. */
    public static function run(Order $order): void
    {
        $order->refresh();

        if ($order->wasFulfilled ?? false) {
            return;
        }

        DB::transaction(function () use ($order) {
            foreach ($order->items()->with('product')->get() as $item) {
                if ($item->product && $item->product->stock_status !== 'on_request') {
                    InventoryMovement::record(
                        $item->product,
                        -$item->qty,
                        'sale',
                        $order->number,
                        "Order {$order->number}",
                    );
                }
            }

            if ($order->status === 'paid') {
                $order->update(['status' => 'processing']);
            }
        });

        try {
            if ($order->customer_email) {
                Notification::route('mail', $order->customer_email)->notify(new OrderConfirmed($order));
            }
        } catch (\Throwable $e) {
            Log::warning('Order confirmation notification failed', ['order' => $order->number, 'error' => $e->getMessage()]);
        }
    }
}
