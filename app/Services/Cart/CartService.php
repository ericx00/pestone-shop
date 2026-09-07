<?php

namespace App\Services\Cart;

use App\Models\Cart;
use App\Models\Product;
use App\Models\Setting;
use App\Services\Pricing\PriceCalculator;
use Illuminate\Support\Facades\Auth;

class CartService
{
    protected ?Cart $cart = null;

    public function __construct(protected PriceCalculator $pricing) {}

    public function current(): Cart
    {
        if ($this->cart) {
            return $this->cart;
        }

        if (Auth::check()) {
            $cart = Cart::firstOrCreate(['user_id' => Auth::id()]);
            $this->mergeSessionCartInto($cart);
        } else {
            $cart = Cart::firstOrCreate(['session_id' => session()->getId(), 'user_id' => null]);
        }

        return $this->cart = $cart->load('items.product.brand', 'items.product.category');
    }

    protected function mergeSessionCartInto(Cart $cart): void
    {
        $sessionCart = Cart::where('session_id', session()->getId())
            ->whereNull('user_id')
            ->with('items')
            ->first();

        if (! $sessionCart || $sessionCart->id === $cart->id) {
            return;
        }

        foreach ($sessionCart->items as $item) {
            $this->addToCart($cart, $item->product_id, $item->qty);
        }
        $sessionCart->delete();
    }

    public function add(int $productId, int $qty = 1): void
    {
        $this->addToCart($this->current(), $productId, $qty);
        $this->refresh();
    }

    protected function addToCart(Cart $cart, int $productId, int $qty): void
    {
        $product = Product::purchasable()->find($productId);
        if (! $product) {
            return;
        }

        $item = $cart->items()->firstOrNew(['product_id' => $productId]);
        $item->qty = max(1, ($item->qty ?? 0) + $qty);
        $item->save();
    }

    public function updateQty(int $productId, int $qty): void
    {
        $cart = $this->current();
        $item = $cart->items()->where('product_id', $productId)->first();
        if (! $item) {
            return;
        }
        if ($qty <= 0) {
            $item->delete();
        } else {
            $item->update(['qty' => $qty]);
        }
        $this->refresh();
    }

    public function remove(int $productId): void
    {
        $this->current()->items()->where('product_id', $productId)->delete();
        $this->refresh();
    }

    public function clear(): void
    {
        $this->current()->items()->delete();
        $this->refresh();
    }

    public function count(): int
    {
        return (int) $this->current()->items->sum('qty');
    }

    public function isEmpty(): bool
    {
        return $this->current()->items->isEmpty();
    }

    /** @return array{lines: array, subtotal:int, discount:int, vat:int, total:int, count:int} */
    public function summary(string $deliveryZone = 'pickup'): array
    {
        $user = Auth::user();
        $lines = [];
        $subtotalNet = 0;
        $vat = 0;
        $discount = 0;
        $listNetTotal = 0;

        foreach ($this->current()->items as $item) {
            if (! $item->product) {
                continue;
            }
            $price = $this->pricing->for($item->product, $user);
            $lineNet = $price->net * $item->qty;
            $lineVat = $price->vat * $item->qty;
            $subtotalNet += $lineNet;
            $vat += $lineVat;

            // discount expressed in the shopper's frame
            $listNet = $price->isB2B || ! $price->pricesIncludeVat
                ? $price->listPrice
                : (int) round($price->listPrice / (1 + (float) Setting::value('vat_rate', 16) / 100));
            $listNetTotal += $listNet * $item->qty;
            $discount += max(0, ($listNet - $price->net)) * $item->qty;

            $lines[] = [
                'product' => $item->product,
                'qty' => $item->qty,
                'unit_net' => $price->net,
                'unit_gross' => $price->gross,
                'line_net' => $lineNet,
                'line_gross' => ($price->net + $price->vat) * $item->qty,
                'price' => $price,
            ];
        }

        $shipping = $this->deliveryFee($deliveryZone, $subtotalNet + $vat);
        $grand = $subtotalNet + $vat + $shipping;

        return [
            'lines' => $lines,
            'subtotal' => $subtotalNet,
            'discount' => $discount,
            'vat' => $vat,
            'shipping' => $shipping,
            'total' => $grand,
            'count' => $this->count(),
        ];
    }

    public function deliveryFee(string $zone, int $orderGross): int
    {
        $fees = (array) Setting::value('delivery_fees', []);
        $fee = (int) ($fees[$zone] ?? 0);
        $threshold = (int) Setting::value('free_delivery_threshold', 0);

        if ($zone !== 'pickup' && $threshold > 0 && $orderGross >= $threshold) {
            return 0;
        }

        return $fee;
    }

    public function refresh(): void
    {
        $this->cart = null;
        $this->current();
    }
}
