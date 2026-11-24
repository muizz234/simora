<?php

namespace Tests\Feature;

use App\Models\DomainEvent;
use App\Modules\Shared\Events\DomainEventType;
use App\Support\DomainEventRecorder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class DomainEventRelayTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'microservices.event_transport' => 'http',
            'microservices.event_webhook' => 'https://events.test/ingest',
            'microservices.outbox.max_attempts' => 2,
            'microservices.outbox.backoff_seconds' => 1,
            'microservices.circuit_breaker.failure_threshold' => 2,
            'microservices.circuit_breaker.cooldown_seconds' => 60,
        ]);

        Http::preventStrayRequests();
    }

    public function test_relay_marks_event_as_published_after_successful_delivery(): void
    {
        Http::fake(['https://events.test/ingest' => Http::response([], 200)]);
        $event = $this->recordEvent();

        $this->artisan('domain-events:relay')->assertSuccessful();

        $this->assertNotNull($event->fresh()->published_at);
        $this->assertSame(0, (int) $event->fresh()->attempts);
        Http::assertSentCount(1);
    }

    public function test_failed_delivery_increments_attempts_and_schedules_retry(): void
    {
        Http::fake(['https://events.test/ingest' => Http::response([], 500)]);
        $event = $this->recordEvent();

        $this->artisan('domain-events:relay')->assertSuccessful();

        $event->refresh();
        $this->assertNull($event->published_at);
        $this->assertSame(1, (int) $event->attempts);
        $this->assertNotNull($event->next_attempt_at);
        $this->assertNotEmpty($event->last_error);
        $this->assertNull($event->dead_lettered_at);
    }

    public function test_event_enters_dead_letter_after_max_attempts(): void
    {
        Http::fake(['https://events.test/ingest' => Http::response([], 503)]);
        $event = $this->recordEvent();

        $this->artisan('domain-events:relay')->assertSuccessful();
        $this->travel(2)->seconds();
        $this->artisan('domain-events:relay')->assertSuccessful();

        $event->refresh();
        $this->assertSame(2, (int) $event->attempts);
        $this->assertNotNull($event->dead_lettered_at);
        $this->assertNull($event->next_attempt_at);
        Http::assertSentCount(2);
    }

    public function test_replay_command_requeues_dead_lettered_event(): void
    {
        Http::fake([
            'https://events.test/ingest' => Http::sequence()
                ->push([], 503)
                ->push([], 503)
                ->push([], 200),
        ]);
        $event = $this->recordEvent();

        $this->artisan('domain-events:relay')->assertSuccessful();
        $this->travel(2)->seconds();
        $this->artisan('domain-events:relay')->assertSuccessful();
        $this->assertNotNull($event->fresh()->dead_lettered_at);

        $this->artisan('domain-events:replay-dead-letter')->assertSuccessful();

        $event->refresh();
        $this->assertNull($event->dead_lettered_at);
        $this->assertSame(0, (int) $event->attempts);
        $this->assertNull($event->last_error);

        $this->artisan('domain-events:relay')->assertSuccessful();
        $this->assertNotNull($event->fresh()->published_at);
    }

    public function test_open_circuit_breaker_skips_relay_without_calling_webhook(): void
    {
        config(['microservices.outbox.max_attempts' => 5]);
        Http::fake(['https://events.test/ingest' => Http::response([], 503)]);
        $event = $this->recordEvent();

        $this->artisan('domain-events:relay')->assertSuccessful();
        $this->travel(2)->seconds();
        $this->artisan('domain-events:relay')->assertSuccessful();
        $this->assertNull($event->fresh()->published_at);

        $this->artisan('domain-events:relay')->assertSuccessful();

        Http::assertSentCount(2);
        $this->assertNull($event->fresh()->published_at);
    }

    public function test_outbox_accepts_event_for_organization_not_yet_available_locally(): void
    {
        $event = DomainEvent::create([
            'event_id' => (string) Str::uuid(),
            'type' => DomainEventType::OrgmateMemberCreated->value,
            'organization_id' => 999999,
            'occurred_at' => now(),
        ]);

        $this->assertDatabaseHas('domain_events', [
            'id' => $event->id,
            'organization_id' => 999999,
        ]);
    }

    private function recordEvent(): DomainEvent
    {
        return DomainEventRecorder::record(
            DomainEventType::SentraBudgetCreated,
            'App\\Models\\Budget',
            42,
            null,
            ['program_name' => 'RAB Seminar'],
        );
    }
}
