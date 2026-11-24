<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\DomainEvent;
use App\Models\Dues;
use App\Models\Evaluation;
use App\Models\Member;
use App\Models\Organization;
use App\Modules\Shared\Events\DomainEventType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleOutboxTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_changes_are_recorded_as_orgmate_events(): void
    {
        $organization = Organization::factory()->create();
        $member = Member::factory()->create(['organization_id' => $organization->id]);

        $this->assertDatabaseHas('domain_events', [
            'type' => DomainEventType::OrgmateMemberCreated->value,
            'subject_type' => Member::class,
            'subject_id' => $member->id,
            'organization_id' => $organization->id,
        ]);
    }

    public function test_completed_lpj_is_published_as_a_domain_event(): void
    {
        $organization = Organization::factory()->create();
        $activity = Activity::factory()->create(['organization_id' => $organization->id]);
        $evaluation = Evaluation::factory()->create([
            'organization_id' => $organization->id,
            'activity_id' => $activity->id,
            'type' => Evaluation::TYPE_REPORT,
            'status' => Evaluation::STATUS_DONE,
        ]);

        $this->assertDatabaseHas('domain_events', [
            'type' => DomainEventType::EProgressLpjPublished->value,
            'subject_type' => Evaluation::class,
            'subject_id' => $evaluation->id,
            'organization_id' => $organization->id,
        ]);
    }

    public function test_marking_dues_paid_records_a_paid_event(): void
    {
        $organization = Organization::factory()->create();
        $dues = Dues::factory()->create([
            'organization_id' => $organization->id,
            'status' => Dues::STATUS_UNPAID,
        ]);

        $dues->update([
            'status' => Dues::STATUS_PAID,
            'paid_at' => now(),
        ]);

        $this->assertDatabaseHas('domain_events', [
            'type' => DomainEventType::SentraDuesPaid->value,
            'subject_type' => Dues::class,
            'subject_id' => $dues->id,
            'organization_id' => $organization->id,
        ]);
    }

    public function test_relay_command_marks_pending_events_as_published(): void
    {
        $organization = Organization::factory()->create();
        Member::factory()->create(['organization_id' => $organization->id]);

        $this->assertGreaterThan(0, DomainEvent::query()->whereNull('published_at')->count());

        $this->artisan('domain-events:relay')->assertSuccessful();

        $this->assertSame(0, DomainEvent::query()->whereNull('published_at')->count());
    }
}
