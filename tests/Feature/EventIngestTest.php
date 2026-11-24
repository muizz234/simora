<?php

namespace Tests\Feature;

use App\Models\DomainEvent;
use App\Models\Organization;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EventIngestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['microservices.token' => 'test-service-token']);
    }

    public function test_ingest_rejects_request_without_a_valid_service_token(): void
    {
        $organization = Organization::factory()->create();
        $payload = $this->eventPayload($organization->id);
        $baseline = DomainEvent::query()->count();

        $this->postJson('/api/internal/v1/events', $payload)
            ->assertUnauthorized();

        $this->withHeader('X-Service-Token', 'wrong-token')
            ->postJson('/api/internal/v1/events', $payload)
            ->assertUnauthorized();

        $this->assertSame($baseline, DomainEvent::query()->count());
    }

    public function test_ingest_rejects_invalid_payload(): void
    {
        $this->withToken('test-service-token')
            ->postJson('/api/internal/v1/events', [
                'event_id' => 'not-a-uuid',
                'type' => '',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['event_id', 'type']);

        $this->assertSame(0, DomainEvent::query()->count());
    }

    public function test_ingest_rejects_unknown_organization(): void
    {
        $this->withToken('test-service-token')
            ->postJson('/api/internal/v1/events', $this->eventPayload(999999))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['organization_id']);

        $this->assertSame(0, DomainEvent::query()->count());
    }

    public function test_ingest_stores_event_and_marks_it_as_published(): void
    {
        $organization = Organization::factory()->create();
        $payload = $this->eventPayload($organization->id);

        $this->withToken('test-service-token')
            ->postJson('/api/internal/v1/events', $payload)
            ->assertCreated()
            ->assertJsonPath('data.event_id', $payload['event_id'])
            ->assertJsonPath('data.duplicate', false);

        $event = DomainEvent::query()->where('event_id', $payload['event_id'])->firstOrFail();

        $this->assertSame('orgmate.member.created', $event->type);
        $this->assertSame($organization->id, $event->organization_id);
        $this->assertSame(['name' => 'Rina'], $event->payload);
        $this->assertNotNull($event->published_at);
        $this->assertSame('2026-11-20', $event->occurred_at?->toDateString());
    }

    public function test_ingest_is_idempotent_for_duplicate_event_id(): void
    {
        $organization = Organization::factory()->create();
        $payload = $this->eventPayload($organization->id);
        $baseline = DomainEvent::query()->count();

        $this->withToken('test-service-token')
            ->postJson('/api/internal/v1/events', $payload)
            ->assertCreated();

        $this->withToken('test-service-token')
            ->postJson('/api/internal/v1/events', $payload)
            ->assertOk()
            ->assertJsonPath('data.duplicate', true);

        $this->assertSame($baseline + 1, DomainEvent::query()->count());
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
