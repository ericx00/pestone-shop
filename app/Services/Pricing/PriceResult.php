<?php

namespace App\Services\Pricing;

use App\Models\Offer;

class PriceResult
{
    public function __construct(
        /** List price before any offer, in the shopper's context (gross for B2C, net for B2B). */
        public int $listPrice,
        /** Price after the best applicable offer. */
        public int $effectivePrice,
        /** VAT amount contained in / added to the effective price. */
        public int $vat,
        /** Net (ex-VAT) unit price used for order lines. */
        public int $net,
        /** Gross (VAT-inclusive) unit price shown to B2C. */
        public int $gross,
        public bool $isB2B,
        public bool $pricesIncludeVat,
        public ?Offer $offer = null,
    ) {}

    public function hasDiscount(): bool
    {
        return $this->effectivePrice < $this->listPrice;
    }

    public function discountAmount(): int
    {
        return max(0, $this->listPrice - $this->effectivePrice);
    }

    public function discountPercent(): int
    {
        return $this->listPrice > 0
            ? (int) round($this->discountAmount() / $this->listPrice * 100)
            : 0;
    }

    public function badgeText(): ?string
    {
        if (! $this->offer) {
            return null;
        }

        return $this->offer->badge_text ?: '-'.$this->discountPercent().'%';
    }

    public function badgeColor(): string
    {
        return $this->offer?->badge_color ?: '#E8801A';
    }
}
