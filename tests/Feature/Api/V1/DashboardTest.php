<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_the_dashboard_summary(): void
    {
        $ketuaRw = User::factory()->role('ketua_rw')->create();

        $response = $this->actingAs($ketuaRw, 'sanctum')->getJson(route('api.v1.dashboard'));

        $response->assertOk();
        $response->assertJsonStructure(['data' => ['stats', 'population_pyramid']]);
    }

    public function test_sekretaris_does_not_see_the_treasury_balance(): void
    {
        $sekretaris = User::factory()->role('sekretaris')->create();

        $response = $this->actingAs($sekretaris, 'sanctum')->getJson(route('api.v1.dashboard'));

        $response->assertOk();
        $response->assertJsonPath('data.stats.total_saldo_kas', null);
        $response->assertJsonPath('data.monthly_trend', null);
    }
}
