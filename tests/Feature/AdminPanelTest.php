<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(SettingsSeeder::class);
    }

    public function test_non_admin_cannot_access_panel(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_admin_can_load_dashboard_and_resource_pages(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)->get('/admin')->assertOk();

        foreach ([
            '/admin/products', '/admin/orders', '/admin/categories', '/admin/brands',
            '/admin/offers', '/admin/users', '/admin/quote-requests', '/admin/shop-settings',
        ] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }
}
