<?php

namespace App\Services\Pricing;

use App\Models\Offer;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Collection;

class PriceCalculator
{
    protected ?Collection $offers = null;

    /**
     * Resolve the price view for a product and (optional) shopper.
     *
     * B2C: product.price is treated as VAT-inclusive (gross) when prices_include_vat.
     * B2B (approved): product.b2b_price (or a discount off price) treated as net (ex-VAT).
     */
    public function for(Product $product, ?User $user = null): PriceResult
    {
        $vatRate = (float) Setting::value('vat_rate', 16) / 100;
        $pricesIncludeVat = (bool) Setting::value('prices_include_vat', true);
        $isB2B = $user?->isApprovedB2B() ?? false;

        // --- base list price in the shopper's frame -------------------------
        if ($isB2B) {
            $listPrice = $product->b2b_price
                ?: (int) round($this->netFromGross($product->price, $vatRate, $pricesIncludeVat)
                    * (1 - (float) Setting::value('b2b_discount_percent', 7) / 100));
        } else {
            $listPrice = (int) $product->price;
        }

        // --- best applicable offer ----------------------------------------
        $offer = $this->bestOffer($product, $listPrice);
        $effective = $offer ? $listPrice - $offer->discountOn($listPrice) : $listPrice;

        // --- split net / vat / gross -------------------------------------
        if ($isB2B) {
            $net = $effective;
            $vat = $user->tax_exempt ? 0 : (int) round($net * $vatRate);
            $gross = $net + $vat;
        } elseif ($pricesIncludeVat) {
            $gross = $effective;
            $net = (int) round($gross / (1 + $vatRate));
            $vat = $gross - $net;
        } else {
            $net = $effective;
            $vat = (int) round($net * $vatRate);
            $gross = $net + $vat;
        }

        return new PriceResult(
            listPrice: $listPrice,
            effectivePrice: $effective,
            vat: $vat,
            net: $net,
            gross: $gross,
            isB2B: $isB2B,
            pricesIncludeVat: $pricesIncludeVat,
            offer: $offer,
        );
    }

    protected function netFromGross(int $price, float $vatRate, bool $inclusive): int
    {
        return $inclusive ? (int) round($price / (1 + $vatRate)) : $price;
    }

    protected function bestOffer(Product $product, int $basePrice): ?Offer
    {
        $best = null;
        $bestDiscount = 0;

        foreach ($this->runningOffers() as $offer) {
            if (! $offer->appliesTo($product)) {
                continue;
            }
            $discount = $offer->discountOn($basePrice);
            if ($discount > $bestDiscount || ($discount === $bestDiscount && $best && $offer->priority > $best->priority)) {
                $best = $offer;
                $bestDiscount = $discount;
            }
        }

        return $bestDiscount > 0 ? $best : null;
    }

    protected function runningOffers(): Collection
    {
        return $this->offers ??= Offer::running()
            ->with(['products:id', 'categories:id', 'brands:id'])
            ->orderByDesc('priority')
            ->get();
    }

    public function flush(): void
    {
        $this->offers = null;
    }
}
