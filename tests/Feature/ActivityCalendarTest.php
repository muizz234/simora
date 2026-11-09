<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_can_add_an_activity_and_it_appears_on_the_calendar(): void
    {
        $organization = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]);
        $start = now()->startOfMonth()->addDays(4)->format('Y-m-d\TH:i');

        $this->actingAs($account)
            ->post(route('simora.e-progress.kalender.store'), [
                'name' => 'Pelatihan Kepemimpinan',
                'status' => 'pelaksanaan',
                'start_at' => $start,
            ])
            ->assertRedirect(route('simora.e-progress.kalender'));

        $this->assertDatabaseHas('activities', [
            'organization_id' => $organization->id,
            'name' => 'Pelatihan Kepemimpinan',
        ]);

        $this->actingAs($account)
            ->get(route('simora.e-progress.kalender', [
                'year' => now()->format('Y'),
                'month' => now()->format('m'),
            ]))
            ->assertOk()
            ->assertSee('Pelatihan Kepemimpinan');
    }

    public function test_calendar_shows_january_and_february_2026_grids(): void
    {
        $organization = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]);
        Activity::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Kegiatan Januari',
            'start_at' => '2026-01-15 09:00:00',
        ]);

        $this->actingAs($account)
            ->get(route('simora.e-progress.kalender', ['year' => 2026, 'month' => 1]))
            ->assertOk()
            ->assertSee('Kegiatan Januari');

        $this->actingAs($account)
            ->get(route('simora.e-progress.kalender', ['year' => 2026, 'month' => 2]))
            ->assertOk()
            ->assertDontSee('Kegiatan Januari');
    }

    public function test_organization_cannot_manage_another_organizations_activity(): void
    {
        $own = Organization::factory()->create();
        $other = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $own->id,
        ]);
        $foreign = Activity::factory()->create(['organization_id' => $other->id, 'name' => 'Asing']);

        $this->actingAs($account)
            ->put(route('simora.e-progress.kalender.update', $foreign), [
                'name' => 'Diubah',
                'status' => 'selesai',
                'start_at' => now()->format('Y-m-d\TH:i'),
            ])
            ->assertForbidden();

        $this->actingAs($account)
            ->delete(route('simora.e-progress.kalender.destroy', $foreign))
            ->assertForbidden();

        $this->assertSame('Asing', $foreign->fresh()->name);
    }

    public function test_developer_cannot_write_activities_but_can_view_the_calendar(): void
    {
        $organization = Organization::factory()->create(['name' => 'HIMA-TI', 'slug' => 'hima-ti']);
        Activity::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Lihat Saja',
            'start_at' => now()->startOfMonth()->addDays(3),
        ]);
        $developer = User::factory()->create(['role' => User::ROLE_DEVELOPER]);

        $this->actingAs($developer)
            ->get(route('simora.e-progress.kalender', ['organization' => 'hima-ti']))
            ->assertOk()
            ->assertSee('Lihat Saja');

        $this->actingAs($developer)
            ->post(route('simora.e-progress.kalender.store'), [
                'name' => 'Bala',
                'status' => 'perencanaan',
                'start_at' => now()->format('Y-m-d\TH:i'),
            ])
            ->assertForbidden();
    }
}
