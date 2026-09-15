<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = [
        'number', 'user_id', 'guest_token', 'channel', 'status', 'payment_status', 'payment_method',
        'customer_name', 'customer_email', 'customer_phone', 'customer_company',
        'shipping_address', 'delivery_zone',
        'subtotal', 'discount_total', 'vat_total', 'shipping_total', 'grand_total', 'currency',
        'notes', 'admin_notes', 'placed_at',
    ];

    protected $casts = [
        'shipping_address' => 'array',
        'placed_at' => 'datetime',
        'subtotal' => 'integer',
        'discount_total' => 'integer',
        'vat_total' => 'integer',
        'shipping_total' => 'integer',
        'grand_total' => 'integer',
    ];

    public const STATUSES = [
        'pending', 'awaiting_payment', 'paid', 'processing',
        'shipped', 'completed', 'cancelled', 'refunded',
    ];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (blank($order->guest_token)) {
                $order->guest_token = Str::random(48);
            }
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->latest();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function successfulPayment(): ?Payment
    {
        return $this->payments()->where('status', 'success')->first();
    }

    public static function generateNumber(): string
    {
        // Human-friendly, but NOT a secret — access to an order is guarded by
        // guest_token / ownership / session, never by the number's obscurity.
        do {
            $number = 'PES-'.now()->format('ymd').'-'.str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (static::where('number', $number)->exists());

        return $number;
    }

    /** Signed URL a guest can use to view/pay this order without an account. */
    public function trackingUrl(string $routeName, array $params = []): string
    {
        return route($routeName, array_merge(['order' => $this], $params)).'?ot='.$this->guest_token;
    }

    public function markPaid(string $method): void
    {
        $this->forceFill([
            'payment_status' => 'paid',
            'payment_method' => $method,
            'status' => $this->status === 'pending' || $this->status === 'awaiting_payment' ? 'paid' : $this->status,
        ])->save();
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function getRouteKeyName(): string
    {
        return 'number';
    }

    public function marginTotal(): int
    {
        return $this->items->sum(fn (OrderItem $i) => ($i->unit_price - $i->cost_price_snapshot) * $i->qty);
    }
}
