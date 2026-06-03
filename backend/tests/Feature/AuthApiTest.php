<?php

namespace Tests\Feature;

use App\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function testMarioCanLoginWithTypedCredentials(): void
    {
        UserModel::factory()
            ->mario()
            ->create();

        $response = $this->withFrontendOrigin()->postJson('/api/v1/sessions', [
            'email' => 'mario@pizzaplanet.test',
            'password' => 'ilovepizza',
        ]);

        $response->assertOk()
            ->assertJsonPath('user.name', 'Mario')
            ->assertJsonPath('user.email', 'mario@pizzaplanet.test');
    }

    public function testCsrfCookieRouteAllowsFrontendCorsPreflight(): void
    {
        $response = $this
            ->withHeader('Origin', 'http://127.0.0.1:5173')
            ->withHeader('Access-Control-Request-Method', 'GET')
            ->options('/sanctum/csrf-cookie');

        $response->assertNoContent()
            ->assertHeader('Access-Control-Allow-Origin', 'http://127.0.0.1:5173')
            ->assertHeader('Access-Control-Allow-Credentials', 'true');
    }

    public function testLoginRejectsInvalidCredentials(): void
    {
        UserModel::factory()->create([
            'email' => 'mario@pizzaplanet.test',
            'password' => Hash::make('ilovepizza'),
        ]);

        $response = $this->withFrontendOrigin()->postJson('/api/v1/sessions', [
            'email' => 'mario@pizzaplanet.test',
            'password' => 'wrong-password',
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('email');
    }

    public function testCurrentUserRequiresAuthentication(): void
    {
        $response = $this->getJson('/api/v1/session');

        $response->assertUnauthorized();
    }

    public function testCurrentUserReturnsAuthenticatedUser(): void
    {
        $user = UserModel::factory()->create([
            'name' => 'Mario',
            'email' => 'mario@pizzaplanet.test',
        ]);

        $response = $this->actingAs($user)->getJson('/api/v1/session');

        $response->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.name', 'Mario')
            ->assertJsonPath('user.email', 'mario@pizzaplanet.test');
    }

    public function testLogoutClearsAuthenticatedSession(): void
    {
        $user = UserModel::factory()->create();

        $response = $this->actingAs($user)->withFrontendOrigin()->deleteJson('/api/v1/session');

        $response->assertNoContent();
    }

    private function withFrontendOrigin(): static
    {
        return $this->withHeader('Origin', 'http://127.0.0.1:5173');
    }
}
