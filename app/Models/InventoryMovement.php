<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    protected $fillable = [
        'product_id', 'change', 'reason', 'reference', 'user_id', 'note',
    ];

    protected $casts = ['change' => 'integer'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Record a movement and adjust the product stock atomically. */
    public static function record(Product $product, int $change, string $reason, ?string $reference = null, ?string $note = null, ?int $userId = null): self
    {
        $movement = static::create([
            'product_id' => $product->id,
            'change' => $change,
            'reason' => $reason,
            'reference' => $reference,
            'note' => $note,
            'user_id' => $userId,
        ]);

        $product->stock_qty = max(0, $product->stock_qty + $change);
        $product->save();

        return $movement;
    }
}
