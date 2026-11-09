<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ApiTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_protected_api_endpoint_rejects_missing_token(): void
    {
        $this->getJson(route('api.v1.me'))->assertUnauthorized();
    }

    public function test_protected_api_endpoint_rejects_wrong_token(): void
    {
        $this->withHeader('Authorization', 'Bearer token-salah')
            ->getJson(route('api.v1.me'))
            ->assertUnauthorized();
    }

    public function test_protected_api_endpoint_accepts_valid_token_and_returns_profile(): void
    {
        $organization = Organization::factory()->create(['name' => 'HIMA-TI']);
        $user = User::factory()->create([
            'name' => 'HIMA-TI',
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
            'api_token' => Str::random(60),
        ]);

        $this->withHeader('Authorization', 'Bearer '.$user->api_token)
            ->getJson(route('api.v1.me'))
            ->assertOk()
            ->assertJsonPath('name', 'HIMA-TI')
            ->assertJsonPath('role', User::ROLE_ORGANIZATION)
            ->assertJsonPath('organization_id', $organization->id)
            ->assertJsonMissingPath('api_token');
    }
}
