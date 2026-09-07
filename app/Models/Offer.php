<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Offer extends Model
{
    protected $fillable = [
        'name', 'badge_text', 'badge_color', 'type', 'value', 'scope',
        'starts_at', 'ends_at', 'priority', 'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'priority' => 'integer',
        'is_active' => 'boolean',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function brands(): BelongsToMany
    {
        return $this->belongsToMany(Brand::class);
    }

    public function scopeRunning(Builder $query): Builder
    {
        $now = now();

        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now));
    }

    public function appliesTo(Product $product): bool
    {
        return match ($this->scope) {
            'all' => true,
            'brand' => $this->brands->contains($product->brand_id),
            'category' => (bool) $this->categories
                ->pluck('id')
                ->intersect([$product->category_id])
                ->count() || $this->matchesCategoryTree($product),
            default => $this->products->contains($product->id),
        };
    }

    protected function matchesCategoryTree(Product $product): bool
    {
        if (! $product->category_id) {
            return false;
        }
        $offerCategoryIds = $this->categories->pluck('id')->all();
        $cat = $product->category;
        while ($cat) {
            if (in_array($cat->id, $offerCategoryIds, true)) {
                return true;
            }
            $cat = $cat->parent;
        }

        return false;
    }

    /** Discount amount (KES) applied to a given base price. */
    public function discountOn(int $basePrice): int
    {
        $amount = $this->type === 'percent'
            ? (int) round($basePrice * ((float) $this->value) / 100)
            : (int) round((float) $this->value);

        return max(0, min($amount, $basePrice));
    }
}
