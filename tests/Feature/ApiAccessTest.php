<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class ApiAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_super_admin_cannot_view_the_page(): void
    {
        $ketuaRw = User::factory()->role('ketua_rw')->create();

        $this->actingAs($ketuaRw)->get(route('api-access.index'))->assertForbidden();
    }

    public function test_super_admin_can_view_the_page(): void
    {
        $superAdmin = User::factory()->role('super_admin')->create();

        $response = $this->actingAs($superAdmin)->get(route('api-access.index'));

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page->has('users.data'));
    }

    public function test_super_admin_can_create_a_new_account_for_a_developer(): void
    {
        $superAdmin = User::factory()->role('super_admin')->create();

        $response = $this->actingAs($superAdmin)->post(route('api-access.users.store'), [
            'name' => 'Integrasi Kelurahan',
            'email' => 'integrasi@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'sekretaris',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'email' => 'integrasi@example.com',
            'role' => 'sekretaris',
            'is_active' => true,
        ]);
        $this->assertTrue(Hash::check('password123', User::where('email', 'integrasi@example.com')->first()->password));
    }

    public function test_non_super_admin_cannot_create_an_account(): void
    {
        $ketuaRw = User::factory()->role('ketua_rw')->create();

        $this->actingAs($ketuaRw)->post(route('api-access.users.store'), [
            'name' => 'Coba',
            'email' => 'coba@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'warga',
        ])->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'coba@example.com']);
    }

    public function test_email_must_be_unique_when_creating_an_account(): void
    {
        $superAdmin = User::factory()->role('super_admin')->create();
        $existing = User::factory()->create();

        $this->actingAs($superAdmin)->post(route('api-access.users.store'), [
            'name' => 'Duplikat',
            'email' => $existing->email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'warga',
        ])->assertSessionHasErrors('email');
    }

    public function test_super_admin_can_issue_a_token_for_any_user(): void
    {
        $superAdmin = User::factory()->role('super_admin')->create();
        $sekretaris = User::factory()->role('sekretaris')->create();

        $response = $this->actingAs($superAdmin)->post(
            route('api-access.tokens.store', $sekretaris),
            ['name' => 'Integrasi Server'],
        );

        $response->assertRedirect(route('api-access.index', ['user_id' => $sekretaris->id]));
        $response->assertSessionHas('newApiToken');
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_id' => $sekretaris->id,
            'tokenable_type' => User::class,
            'name' => 'Integrasi Server',
        ]);
    }

    public function test_non_super_admin_cannot_issue_a_token(): void
    {
        $ketuaRw = User::factory()->role('ketua_rw')->create();
        $sekretaris = User::factory()->role('sekretaris')->create();

        $this->actingAs($ketuaRw)
            ->post(route('api-access.tokens.store', $sekretaris), ['name' => 'Coba'])
            ->assertForbidden();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_token_issued_for_a_user_authenticates_as_that_user_with_their_own_rbac(): void
    {
        $superAdmin = User::factory()->role('super_admin')->create();
        $sekretaris = User::factory()->role('sekretaris')->create();

        $this->actingAs($superAdmin)->post(
            route('api-access.tokens.store', $sekretaris),
            ['name' => 'Uji RBAC'],
        );
        $plainTextToken = session('newApiToken');

        // Sanctum's guard checks the `web` session guard before the bearer
        // token (config('sanctum.guard')) — lepas guard super_admin dari
        // actingAs() di atas dulu, supaya request berikutnya benar-benar
        // diautentikasi lewat token, bukan sesi web yang masih membekas.
        $this->app['auth']->forgetGuards();

        $this->withToken($plainTextToken)->getJson(route('api.v1.user'))
            ->assertOk()
            ->assertJsonPath('data.email', $sekretaris->email);

        // Sekretaris tidak berhak mengakses modul Keuangan (paritas RBAC web).
        $this->withToken($plainTextToken)->getJson(route('api.v1.treasuries.index'))
            ->assertForbidden();
    }

    public function test_super_admin_can_revoke_any_users_token(): void
    {
        $superAdmin = User::factory()->role('super_admin')->create();
        $sekretaris = User::factory()->role('sekretaris')->create();
        $token = $sekretaris->createToken('Lama');

        $response = $this->actingAs($superAdmin)->delete(route('api-access.tokens.destroy', $token->accessToken->id));

        $response->assertRedirect(route('api-access.index', ['user_id' => $sekretaris->id]));
        $this->assertModelMissing($token->accessToken);
    }

    public function test_non_super_admin_cannot_revoke_a_token(): void
    {
        $ketuaRw = User::factory()->role('ketua_rw')->create();
        $sekretaris = User::factory()->role('sekretaris')->create();
        $token = $sekretaris->createToken('Lama');

        $this->actingAs($ketuaRw)
            ->delete(route('api-access.tokens.destroy', $token->accessToken->id))
            ->assertForbidden();

        $this->assertModelExists($token->accessToken);
    }

    public function test_revoked_token_can_no_longer_authenticate(): void
    {
        $superAdmin = User::factory()->role('super_admin')->create();
        $sekretaris = User::factory()->role('sekretaris')->create();
        $token = $sekretaris->createToken('Lama');

        $this->actingAs($superAdmin)->delete(route('api-access.tokens.destroy', $token->accessToken->id));

        $this->assertSame(0, PersonalAccessToken::query()->count());
    }
}
