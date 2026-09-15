<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeployControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_route_404s_without_a_configured_token(): void
    {
        config(['app.deploy_token' => null]);

        $this->get('/deploy/anything')->assertNotFound();
    }

    public function test_route_404s_with_the_wrong_token(): void
    {
        config(['app.deploy_token' => 'secret-token']);

        $this->get('/deploy/wrong-token')->assertNotFound();
    }

    public function test_correct_token_runs_migrate_seed_and_import(): void
    {
        config(['app.deploy_token' => 'secret-token', 'app.web_root' => null]);

        $res = $this->get('/deploy/secret-token?seed=1');

        $res->assertOk();
        $res->assertSee('migrate --force');
        $res->assertSee('Done.');
        $this->assertDatabaseHas('settings', ['key' => 'markup_percent']);
    }
}
