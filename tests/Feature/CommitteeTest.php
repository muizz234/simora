<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Committee;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommitteeTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_can_add_committee_members_to_an_activity(): void
    {
        $organization = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]);
        $activity = Activity::factory()->create(['organization_id' => $organization->id, 'name' => 'Pelatihan']);

        $this->actingAs($account)
            ->post(route('simora.e-progress.kepanitiaan.store'), [
                'activity_id' => $activity->id,
                'name' => 'Andi Pratama',
                'role' => 'Ketua Pelaksana',
            ])
            ->assertRedirect(route('simora.e-progress.kepanitiaan', ['activity' => $activity->id]));

        $this->assertDatabaseHas('committees', [
            'organization_id' => $organization->id,
            'activity_id' => $activity->id,
            'name' => 'Andi Pratama',
            'role' => 'Ketua Pelaksana',
        ]);

        $this->actingAs($account)
            ->get(route('simora.e-progress.kepanitiaan', ['activity' => $activity->id]))
            ->assertOk()
            ->assertSee('Andi Pratama')
            ->assertSee('Pelatihan');
    }

    public function test_committee_must_belong_to_own_organization_activity(): void
    {
        $own = Organization::factory()->create();
        $other = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $own->id,
        ]);
        $foreignActivity = Activity::factory()->create(['organization_id' => $other->id]);

        $this->actingAs($account)
            ->post(route('simora.e-progress.kepanitiaan.store'), [
                'activity_id' => $foreignActivity->id,
                'name' => 'Bala',
                'role' => 'Anggota',
            ])
            ->assertSessionHasErrors('activity_id');
    }

    public function test_organization_cannot_manage_another_organizations_committee(): void
    {
        $own = Organization::factory()->create();
        $other = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $own->id,
        ]);
        $foreign = Committee::factory()->create(['organization_id' => $other->id, 'name' => 'Asing']);

        $this->actingAs($account)
            ->put(route('simora.e-progress.kepanitiaan.update', $foreign), [
                'name' => 'Diubah',
                'role' => 'Ketua Pelaksana',
            ])
            ->assertForbidden();

        $this->actingAs($account)
            ->delete(route('simora.e-progress.kepanitiaan.destroy', $foreign))
            ->assertForbidden();

        $this->assertSame('Asing', $foreign->fresh()->name);
    }

    public function test_developer_cannot_write_committees_but_can_view_them(): void
    {
        $organization = Organization::factory()->create(['name' => 'HIMA-TI', 'slug' => 'hima-ti']);
        $activity = Activity::factory()->create(['organization_id' => $organization->id, 'name' => 'Pelatihan']);
        Committee::factory()->create(['organization_id' => $organization->id, 'activity_id' => $activity->id, 'name' => 'Lihat Saja']);
        $developer = User::factory()->create(['role' => User::ROLE_DEVELOPER]);

        $this->actingAs($developer)
            ->get(route('simora.e-progress.kepanitiaan', ['organization' => 'hima-ti', 'activity' => $activity->id]))
            ->assertOk()
            ->assertSee('Lihat Saja');

        $this->actingAs($developer)
            ->post(route('simora.e-progress.kepanitiaan.store'), [
                'activity_id' => $activity->id,
                'name' => 'Bala',
                'role' => 'Anggota',
            ])
            ->assertForbidden();
    }
}
