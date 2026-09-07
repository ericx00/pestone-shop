<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Offer;
use App\Models\Product;
use App\Models\User;
use App\Services\Pricing\PriceCalculator;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PricingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SettingsSeeder::class);
    }

    protected function product(array $attrs = []): Product
    {
        return Product::create(array_merge([
            'name' => 'Test', 'slug' => 'test-'.uniqid(),
            'cost_price' => 10000, 'price' => 11600, 'stock_status' => 'in_stock', 'stock_qty' => 5,
        ], $attrs));
    }

    public function test_b2c_price_is_vat_inclusive_and_net_matches_markup(): void
    {
        $p = $this->product(['cost_price' => 10000, 'price' => 11600]);
        $r = app(PriceCalculator::class)->for($p);

        $this->assertSame(11600, $r->gross);
        $this->assertSame(10000, $r->net);
        $this->assertSame(1600, $r->vat);
        $this->assertFalse($r->isB2B);
    }

    public function test_approved_b2b_sees_ex_vat_discounted_price(): void
    {
        $p = $this->product(['cost_price' => 10000, 'price' => 11600]);
        $user = User::factory()->create(['type' => 'b2b', 'b2b_status' => 'approved']);

        $r = app(PriceCalculator::class)->for($p, $user);

        $this->assertTrue($r->isB2B);
        // 7% off net 10000 = 9300, + VAT
        $this->assertSame(9300, $r->net);
        $this->assertSame(1488, $r->vat);
    }

    public function test_percentage_offer_applies_best_discount(): void
    {
        $p = $this->product(['cost_price' => 10000, 'price' => 11600]);
        $offer = Offer::create([
            'name' => '10% off', 'type' => 'percent', 'value' => 10, 'scope' => 'products',
            'is_active' => true, 'badge_text' => 'DEAL',
        ]);
        $offer->products()->attach($p);

        app()->forgetInstance(PriceCalculator::class);
        $r = app(PriceCalculator::class)->for($p->fresh());

        $this->assertSame(10440, $r->effectivePrice); // 11600 - 10%
        $this->assertTrue($r->hasDiscount());
        $this->assertSame('DEAL', $r->badgeText());
    }

    public function test_category_offer_matches_child_category_products(): void
    {
        $parent = Category::create(['name' => 'Computing', 'slug' => 'computing']);
        $child = Category::create(['name' => 'Laptops', 'slug' => 'laptops', 'parent_id' => $parent->id]);
        $p = $this->product(['category_id' => $child->id]);

        $offer = Offer::create([
            'name' => 'Cat sale', 'type' => 'percent', 'value' => 5, 'scope' => 'category', 'is_active' => true,
        ]);
        $offer->categories()->attach($parent);

        app()->forgetInstance(PriceCalculator::class);
        $r = app(PriceCalculator::class)->for($p->fresh());

        $this->assertTrue($r->hasDiscount());
    }
}
