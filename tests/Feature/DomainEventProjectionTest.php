<?php

namespace Tests\Feature;

use App\Models\EventProjection;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DomainEventProjectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['microservices.token' => 'test-service-token']);
    }

    public function test_ingested_event_creates_local_projection(): void
    {
        $organization = Organization::factory()->create();
        $payload = $this->eventPayload($organization->id, ['payload' => ['name' => 'Rina']]);

        $this->withToken('test-service-token')
            ->postJson('/api/internal/v1/events', $payload)
            ->assertCreated();

        $this->assertDatabaseHas('event_projections', [
            'subject_type' => 'App\\Models\\Member',
            'subject_id' => 12,
            'organization_id' => $organization->id,
            'label' => 'Rina',
        ]);

        $projection = EventProjection::query()->firstOrFail();
        $this->assertSame(['name' => 'Rina'], $projection->payload);
        $this->assertSame('2026-11-20', $projection->last_event_at->toDateString());
    }

    public function test_older_event_does_not_overwrite_newer_projection(): void
    {
        $organization = Organization::factory()->create();

        $this->withToken('test-service-token')
            ->postJson('/api/internal/v1/events', $this->eventPayload($organization->id, [
                'payload' => ['name' => 'Siti'],
                'occurred_at' => '2026-11-22T08:30:00+00:00',
            ]))
            ->assertCreated();

        $this->withToken('test-service-token')
            ->postJson('/api/internal/v1/events', $this->eventPayload($organization->id, [
                'payload' => ['name' => 'Rina'],
                'occurred_at' => '2026-11-21T08:30:00+00:00',
            ]))
            ->assertCreated();

        $this->assertSame(1, EventProjection::query()->count());
        $this->assertSame('Siti', EventProjection::query()->firstOrFail()->label);
    }

    public function test_delete_event_removes_projection(): void
    {
        $organization = Organization::factory()->create();

        $this->withToken('test-service-token')
            ->postJson('/api/internal/v1/events', $this->eventPayload($organization->id))
            ->assertCreated();

        $this->assertSame(1, EventProjection::query()->count());

        $this->withToken('test-service-token')
            ->postJson('/api/internal/v1/events', $this->eventPayload($organization->id, [
                'type' => 'orgmate.member.deleted',
            ]))
            ->assertCreated();

        $this->assertSame(0, EventProjection::query()->count());
    }

    public function test_duplicate_delivery_keeps_single_projection(): void
    {
        $organization = Organization::factory()->create();
        $payload = $this->eventPayload($organization->id);

        $this->withToken('test-service-token')->postJson('/api/internal/v1/events', $payload)->assertCreated();
        $this->withToken('test-service-token')->postJson('/api/internal/v1/events', $payload)->assertOk();

        $this->assertSame(1, EventProjection::query()->count());
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function eventPayload(int $organizationId, array $overrides = []): array
    {
        return array_merge([
            'event_id' => (string) Str::uuid(),
            'type' => 'orgmate.member.created',
            'subject_type' => 'App\\Models\\Member',
            'subject_id' => 12,
            'organization_id' => $organizationId,
            'payload' => ['name' => 'Rina'],
            'occurred_at' => '2026-11-20T08:30:00+00:00',
        ], $overrides);
    }
}
