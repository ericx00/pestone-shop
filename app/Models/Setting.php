<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    protected $casts = ['value' => 'array'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = Cache::rememberForever('settings.all', fn () => static::pluck('value', 'key')->toArray());

        return array_key_exists($key, $all) ? $all[$key] : $default;
    }

    public static function put(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('settings.all');
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('settings.all'));
        static::deleted(fn () => Cache::forget('settings.all'));
    }

    /** Defaults used when a row is absent. */
    public static function defaults(): array
    {
        return [
            'markup_percent' => 10,
            'vat_rate' => 16,
            'prices_include_vat' => true,
            'low_stock_threshold' => 3,
            'free_delivery_threshold' => 100000,
            'delivery_fees' => [
                'nairobi_cbd' => 300,
                'nairobi_metro' => 500,
                'countrywide' => 1000,
                'pickup' => 0,
            ],
            'b2b_discount_percent' => 7,
            'company' => [
                'name' => 'Pestone Technologies Ltd',
                'tagline' => 'Proven Technology Solutions',
                'email' => 'sales@pestone.co.ke',
                'phone' => '+254 735 120 752',
                'po_box' => 'P.O. Box 33246-00600, Nairobi',
                'website' => 'www.pestone.co.ke',
            ],
            'payment_methods' => ['mpesa', 'pesapal'],
        ];
    }

    public static function value(string $key, mixed $fallback = null): mixed
    {
        $defaults = static::defaults();

        return static::get($key, $defaults[$key] ?? $fallback);
    }
}
