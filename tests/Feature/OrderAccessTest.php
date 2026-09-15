<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SettingsSeeder::class);
    }

    protected function placeGuestOrder(): Order
    {
        $product = Product::create([
            'name' => 'Guarded Item', 'slug' => 'guarded-item', 'cost_price' => 1000, 'price' => 1160,
            'stock_status' => 'in_stock', 'stock_qty' => 5,
        ]);
        $this->post('/cart/add', ['product_id' => $product->id, 'qty' => 1]);
        $this->post('/checkout', [
            'customer_name' => 'Victim', 'customer_phone' => '0712345678', 'customer_email' => 'v@example.com',
            'line1' => 'Somewhere', 'town' => 'Nairobi', 'delivery_zone' => 'pickup', 'payment_method' => 'manual',
        ]);

        return Order::first();
    }

    public function test_order_number_alone_does_not_grant_access(): void
    {
        $order = $this->placeGuestOrder();

        // Fresh, unrelated "attacker" session — no ?ot=, no prior session flag.
        $this->app['session']->flush();
        $this->get(route('checkout.return', $order))->assertForbidden();
        $this->get(route('checkout.pay.form', $order))->assertForbidden();
        $this->getJson(route('checkout.status', $order))->assertForbidden();
        $this->post(route('checkout.pay', $order), ['payment_method' => 'manual'])->assertForbidden();
    }

    public function test_same_session_that_placed_the_order_can_view_it(): void
    {
        $order = $this->placeGuestOrder();

        // No session flush — this is the checkout session itself.
        $this->get(route('checkout.return', $order))->assertOk();
    }

    public function test_correct_guest_token_grants_access_from_any_session(): void
    {
        $order = $this->placeGuestOrder();

        $this->app['session']->flush();
        $this->get($order->trackingUrl('checkout.return'))->assertOk();
    }

    public function test_wrong_guest_token_is_rejected(): void
    {
        $order = $this->placeGuestOrder();

        $this->app['session']->flush();
        $this->get(route('checkout.return', $order).'?ot=not-the-real-token')->assertForbidden();
    }

    public function test_a_different_logged_in_user_cannot_view_someone_elses_order(): void
    {
        $order = $this->placeGuestOrder();
        $order->update(['user_id' => User::factory()->create()->id]);

        $stranger = User::factory()->create();
        $this->app['session']->flush();
        $this->actingAs($stranger)->get(route('checkout.return', $order))->assertForbidden();
    }

    public function test_the_owning_user_can_view_their_order_without_a_token(): void
    {
        $order = $this->placeGuestOrder();
        $owner = User::factory()->create();
        $order->update(['user_id' => $owner->id]);

        $this->app['session']->flush();
        $this->actingAs($owner)->get(route('checkout.return', $order))->assertOk();
    }
}
