<?php

namespace Tests\Feature;

use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class InternalApiSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'microservices.token' => 'primary-token',
            'microservices.token_expires_at' => null,
            'microservices.token_previous' => null,
            'microservices.token_previous_expires_at' => null,
            'microservices.rate_limit.per_minute' => 120,
        ]);
    }

    public function test_internal_api_rejects_expired_primary_token(): void
    {
        config(['microservices.token_expires_at' => now()->subMinute()->toIso8601String()]);

        $this->withToken('primary-token')
            ->getJson('/api/internal/v1/organizations/1/members')
            ->assertUnauthorized();
    }

    public function test_internal_api_accepts_previous_token_until_rotation_deadline(): void
    {
        config([
            'microservices.token_previous' => 'old-token',
            'microservices.token_previous_expires_at' => now()->addDay()->toIso8601String(),
        ]);
        $organization = Organization::factory()->create();

        $this->withToken('old-token')
            ->getJson("/api/internal/v1/organizations/{$organization->id}/members")
            ->assertOk();

        $this->travel(2)->days();

        $this->withToken('old-token')
            ->getJson("/api/internal/v1/organizations/{$organization->id}/members")
            ->assertUnauthorized();

        $this->withToken('primary-token')
            ->getJson("/api/internal/v1/organizations/{$organization->id}/members")
            ->assertOk();
    }

    public function test_internal_api_limits_requests_per_token(): void
    {
        $token = 'rate-limit-'.Str::uuid();
        config([
            'microservices.token' => $token,
            'microservices.rate_limit.per_minute' => 2,
        ]);
        $organization = Organization::factory()->create();
        $url = "/api/internal/v1/organizations/{$organization->id}/members";

        $this->withToken($token)->getJson($url)->assertOk();
        $this->withToken($token)->getJson($url)->assertOk();
        $this->withToken($token)->getJson($url)->assertStatus(429);
    }

    public function test_rate_limit_is_scoped_per_service_token(): void
    {
        $firstToken = 'first-'.Str::uuid();
        $secondToken = 'second-'.Str::uuid();
        config([
            'microservices.token' => $firstToken,
            'microservices.token_previous' => $secondToken,
            'microservices.rate_limit.per_minute' => 1,
        ]);
        $organization = Organization::factory()->create();
        $url = "/api/internal/v1/organizations/{$organization->id}/members";

        $this->withToken($firstToken)->getJson($url)->assertOk();
        $this->withToken($firstToken)->getJson($url)->assertStatus(429);
        $this->withToken($secondToken)->getJson($url)->assertOk();
    }
}
