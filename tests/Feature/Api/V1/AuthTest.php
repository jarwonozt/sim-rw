<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_valid_credentials_and_receive_a_token(): void
    {
        $user = User::factory()->create(['email' => 'warga@example.com']);

        $response = $this->postJson(route('api.v1.login'), [
            'email' => 'warga@example.com',
            'password' => 'password',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.user.email', 'warga@example.com');
        $this->assertNotEmpty($response->json('data.token'));
        $this->assertSame(1, PersonalAccessToken::query()->count());
    }

    public function test_login_fails_with_an_invalid_password(): void
    {
        User::factory()->create(['email' => 'warga@example.com']);

        $response = $this->postJson(route('api.v1.login'), [
            'email' => 'warga@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('email');
    }

    public function test_inactive_account_cannot_login(): void
    {
        User::factory()->create(['email' => 'nonaktif@example.com', 'is_active' => false]);

        $response = $this->postJson(route('api.v1.login'), [
            'email' => 'nonaktif@example.com',
            'password' => 'password',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('email');
    }

    public function test_protected_endpoint_rejects_a_request_without_a_token(): void
    {
        $this->getJson(route('api.v1.user'))->assertUnauthorized();
    }

    public function test_logout_revokes_the_current_token(): void
    {
        $user = User::factory()->create(['email' => 'warga@example.com']);
        $accessToken = $user->createToken('mobile');

        $this->withToken($accessToken->plainTextToken)->postJson(route('api.v1.logout'))->assertOk();

        $this->assertModelMissing($accessToken->accessToken);
    }
}
