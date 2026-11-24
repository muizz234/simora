<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class HealthCheckTest extends TestCase
{
    public function test_health_endpoint_reports_ok_when_database_is_available(): void
    {
        $response = $this->getJson('/api/health')
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('service', 'simora')
            ->assertJsonPath('database', 'ok');

        $this->assertNotNull($response->json('timestamp'));
    }

    public function test_health_endpoint_reports_503_when_database_is_unavailable(): void
    {
        DB::shouldReceive('select')->once()->with('select 1')->andThrow(new RuntimeException('database down'));

        $this->getJson('/api/health')
            ->assertServiceUnavailable()
            ->assertJsonPath('status', 'degraded')
            ->assertJsonPath('database', 'unavailable');
    }
}
