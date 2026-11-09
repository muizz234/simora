<?php

namespace Tests\Feature;

use App\Models\DomainEvent;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DomainEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_an_organization_records_a_created_event(): void
    {
        $organization = Organization::factory()->create(['name' => 'HIMA-TI', 'slug' => 'hima-ti']);

        $this->assertDatabaseHas('domain_events', [
            'type' => 'organization.created',
            'subject_type' => Organization::class,
            'subject_id' => $organization->id,
            'organization_id' => $organization->id,
        ]);
    }

    public function test_updating_vision_and_mission_records_an_updated_event(): void
    {
        $organization = Organization::factory()->create(['vision' => null, 'mission' => null]);

        DomainEvent::query()->delete();

        $organization->update([
            'vision' => 'Visi baru',
            'mission' => 'Misi baru',
        ]);

        $event = DomainEvent::where('type', 'organization.updated')->first();

        $this->assertNotNull($event);
        $this->assertSame('Visi baru', $event->payload['vision']);
        $this->assertSame('Misi baru', $event->payload['mission']);
    }

    public function test_registering_a_member_records_a_member_event_with_its_organization(): void
    {
        $organization = Organization::factory()->create();
        $member = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]);

        $this->assertDatabaseHas('domain_events', [
            'type' => 'member.registered',
            'subject_type' => User::class,
            'subject_id' => $member->id,
            'organization_id' => $organization->id,
        ]);
    }

    public function test_event_survives_organization_deletion_with_null_organization(): void
    {
        $organization = Organization::factory()->create();

        $organization->delete();

        $this->assertDatabaseHas('domain_events', [
            'type' => 'organization.deleted',
            'subject_id' => $organization->id,
        ]);
        $this->assertNull(DomainEvent::where('type', 'organization.deleted')->first()->organization_id);
    }
}
