<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiGuideTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_view_the_api_guide(): void
    {
        $superAdmin = User::factory()->role('super_admin')->create();

        $response = $this->actingAs($superAdmin)->get(route('api-guide.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->has('groups'));
    }

    public function test_ketua_rw_cannot_view_the_api_guide(): void
    {
        $ketuaRw = User::factory()->role('ketua_rw')->create();

        $this->actingAs($ketuaRw)->get(route('api-guide.index'))->assertForbidden();
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('api-guide.index'))->assertRedirect(route('login'));
    }

    public function test_only_super_admin_can_view_the_interactive_api_docs(): void
    {
        $ketuaRw = User::factory()->role('ketua_rw')->create();
        $superAdmin = User::factory()->role('super_admin')->create();

        $this->actingAs($ketuaRw)->get('/docs/api')->assertForbidden();
        $this->actingAs($superAdmin)->get('/docs/api')->assertOk();
    }
}
