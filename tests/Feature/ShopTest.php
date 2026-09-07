<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SettingsSeeder::class);
    }

    protected function product(): Product
    {
        return Product::create([
            'name' => 'HP Laptop', 'slug' => 'hp-laptop', 'cost_price' => 50000, 'price' => 63000,
            'stock_status' => 'in_stock', 'stock_qty' => 10, 'is_active' => true,
        ]);
    }

    public function test_home_and_shop_load(): void
    {
        $this->product();
        $this->get('/')->assertOk();
        $this->get('/shop')->assertOk()->assertSee('HP Laptop');
    }

    public function test_product_page_loads(): void
    {
        $p = $this->product();
        $this->get("/product/{$p->slug}")->assertOk()->assertSee('HP Laptop');
    }

    public function test_cart_add_and_checkout_creates_order(): void
    {
        $p = $this->product();

        $this->post('/cart/add', ['product_id' => $p->id, 'qty' => 2])->assertRedirect();
        $this->get('/cart')->assertOk()->assertSee('HP Laptop');

        $res = $this->post('/checkout', [
            'customer_name' => 'Jane Doe',
            'customer_phone' => '0712345678',
            'customer_email' => 'jane@example.com',
            'line1' => 'Some Street',
            'town' => 'Nairobi',
            'delivery_zone' => 'pickup',
            'payment_method' => 'manual',
        ]);

        $order = Order::first();
        $this->assertNotNull($order);
        $this->assertSame(2, $order->items->first()->qty);
        $this->assertSame(108620, $order->subtotal);          // 2 x round(63000 / 1.16)
        $this->assertSame(17380, $order->vat_total);
        $this->assertSame(126000, $order->grand_total);       // pickup = no delivery fee
        $res->assertRedirect(route('checkout.pay.form', $order));
    }

    public function test_quote_request_is_stored(): void
    {
        $this->post('/request-a-quote', [
            'name' => 'Buyer', 'phone' => '0712000000', 'message' => 'Need 10 laptops',
        ])->assertRedirect();

        $this->assertDatabaseHas('quote_requests', ['name' => 'Buyer', 'status' => 'new']);
    }
}
