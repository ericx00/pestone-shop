<?php

namespace App\Models;

use App\Services\Pricing\PriceCalculator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'sku', 'name', 'slug', 'brand_id', 'category_id', 'short_description', 'description',
        'cost_price', 'price', 'b2b_price', 'price_locked', 'stock_qty', 'stock_status',
        'availability_label', 'weight_grams', 'images', 'specs', 'is_active', 'is_featured',
        'meta_title', 'meta_description', 'source_sheet',
    ];

    protected $casts = [
        'images' => 'array',
        'specs' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'price_locked' => 'boolean',
        'cost_price' => 'integer',
        'price' => 'integer',
        'b2b_price' => 'integer',
        'stock_qty' => 'integer',
    ];

    protected static function booted(): void
    {
        static::updating(function (Product $product) {
            if ($product->isDirty('price')) {
                $product->price_locked = true;
            }
        });

        static::saving(function (Product $product) {
            $product->syncStockStatus();
        });
    }

    public function syncStockStatus(): void
    {
        $threshold = (int) Setting::get('low_stock_threshold', 3);
        if ($this->stock_status === 'on_request' && $this->stock_qty <= 0) {
            return;
        }
        $this->stock_status = match (true) {
            $this->stock_qty <= 0 => 'out',
            $this->stock_qty <= $threshold => 'low',
            default => 'in_stock',
        };
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function offers(): BelongsToMany
    {
        return $this->belongsToMany(Offer::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class)->latest();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopePurchasable(Builder $query): Builder
    {
        return $query->where('is_active', true)->where('price', '>', 0);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('sku', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });
    }

    /** Pricing view for the current shopper (B2C or approved B2B). */
    public function pricing(?\App\Models\User $user = null): \App\Services\Pricing\PriceResult
    {
        return app(PriceCalculator::class)->for($this, $user);
    }

    public function isOnRequest(): bool
    {
        return $this->stock_status === 'on_request' || ($this->stock_qty <= 0 && $this->stock_status !== 'out');
    }

    public function isInStock(): bool
    {
        return $this->stock_qty > 0;
    }

    public function primaryImage(): ?string
    {
        return $this->images[0] ?? null;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
