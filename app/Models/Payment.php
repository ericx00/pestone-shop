<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'order_id', 'gateway', 'method', 'status', 'amount', 'phone',
        'gateway_ref', 'merchant_request_id', 'checkout_request_id',
        'raw_request', 'raw_response', 'paid_at',
    ];

    protected $casts = [
        'raw_request' => 'array',
        'raw_response' => 'array',
        'paid_at' => 'datetime',
        'amount' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function markSuccess(?string $ref = null, array $payload = []): void
    {
        $this->forceFill([
            'status' => 'success',
            'gateway_ref' => $ref ?? $this->gateway_ref,
            'raw_response' => array_merge($this->raw_response ?? [], $payload),
            'paid_at' => now(),
        ])->save();
    }

    public function markFailed(array $payload = []): void
    {
        $this->forceFill([
            'status' => 'failed',
            'raw_response' => array_merge($this->raw_response ?? [], $payload),
        ])->save();
    }
}
