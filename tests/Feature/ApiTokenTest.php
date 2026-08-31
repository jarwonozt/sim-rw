<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class ApiTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_generate_a_named_api_token(): void
    {
        $superAdmin = User::factory()->role('super_admin')->create();

        $response = $this->actingAs($superAdmin)->post(route('api-tokens.store'), [
            'name' => 'Aplikasi Mobile Android',
        ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('newApiToken');
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $superAdmin->id,
            'tokenable_type' => User::class,
            'name' => 'Aplikasi Mobile Android',
        ]);
    }

    public function test_non_super_admin_cannot_generate_a_token(): void
    {
        $ketuaRw = User::factory()->role('ketua_rw')->create();

        $this->actingAs($ketuaRw)
            ->post(route('api-tokens.store'), ['name' => 'Coba'])
            ->assertForbidden();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_token_name_is_required(): void
    {
        $superAdmin = User::factory()->role('super_admin')->create();

        $this->actingAs($superAdmin)->post(route('api-tokens.store'), ['name' => ''])
            ->assertSessionHasErrors('name');
    }

    public function test_the_generated_token_can_authenticate_api_requests(): void
    {
        $superAdmin = User::factory()->role('super_admin')->create();

        $this->actingAs($superAdmin)->post(route('api-tokens.store'), ['name' => 'Uji Coba']);
        $plainTextToken = session('newApiToken');

        $this->withToken($plainTextToken)->getJson(route('api.v1.user'))->assertOk();
    }

    public function test_super_admin_can_revoke_their_own_token(): void
    {
        $superAdmin = User::factory()->role('super_admin')->create();
        $token = $superAdmin->createToken('Lama');

        $this->actingAs($superAdmin)->delete(route('api-tokens.destroy', $token->accessToken->id));

        $this->assertModelMissing($token->accessToken);
    }

    public function test_super_admin_cannot_revoke_another_super_admins_token(): void
    {
        $owner = User::factory()->role('super_admin')->create();
        $anotherSuperAdmin = User::factory()->role('super_admin')->create();
        $token = $owner->createToken('Milik Owner');

        $this->actingAs($anotherSuperAdmin)
            ->delete(route('api-tokens.destroy', $token->accessToken->id))
            ->assertForbidden();

        $this->assertModelExists($token->accessToken);
    }

    public function test_non_super_admin_cannot_revoke_a_token(): void
    {
        $superAdmin = User::factory()->role('super_admin')->create();
        $ketuaRw = User::factory()->role('ketua_rw')->create();
        $token = $superAdmin->createToken('Milik Super Admin');

        $this->actingAs($ketuaRw)
            ->delete(route('api-tokens.destroy', $token->accessToken->id))
            ->assertForbidden();

        $this->assertModelExists($token->accessToken);
    }

    public function test_revoked_token_can_no_longer_authenticate(): void
    {
        $superAdmin = User::factory()->role('super_admin')->create();
        $token = $superAdmin->createToken('Uji Coba');

        $this->actingAs($superAdmin)->delete(route('api-tokens.destroy', $token->accessToken->id));

        $this->assertSame(0, PersonalAccessToken::query()->count());
    }
}
