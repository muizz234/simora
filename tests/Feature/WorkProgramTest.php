<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use App\Models\WorkProgram;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkProgramTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_can_add_a_work_program_and_it_appears_in_the_list(): void
    {
        $organization = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]);

        $this->actingAs($account)
            ->post(route('simora.e-progress.proker.store'), [
                'name' => 'Program Kerja Tahunan',
                'status' => 'pelaksanaan',
                'start_date' => '2026-01-10',
                'end_date' => '2026-12-10',
            ])
            ->assertRedirect(route('simora.e-progress.proker'));

        $this->assertDatabaseHas('work_programs', [
            'organization_id' => $organization->id,
            'name' => 'Program Kerja Tahunan',
            'created_by' => $account->id,
        ]);

        $this->actingAs($account)
            ->get(route('simora.e-progress.proker'))
            ->assertOk()
            ->assertSee('Program Kerja Tahunan')
            ->assertSee('Pelaksanaan');
    }

    public function test_organization_cannot_manage_another_organizations_work_program(): void
    {
        $own = Organization::factory()->create();
        $other = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $own->id,
        ]);
        $foreign = WorkProgram::factory()->create(['organization_id' => $other->id, 'name' => 'Asing']);

        $this->actingAs($account)
            ->put(route('simora.e-progress.proker.update', $foreign), [
                'name' => 'Diubah',
                'status' => 'selesai',
            ])
            ->assertForbidden();

        $this->actingAs($account)
            ->delete(route('simora.e-progress.proker.destroy', $foreign))
            ->assertForbidden();

        $this->assertSame('Asing', $foreign->fresh()->name);
    }

    public function test_developer_cannot_write_work_programs_but_can_view_them(): void
    {
        $organization = Organization::factory()->create(['name' => 'HIMA-TI', 'slug' => 'hima-ti']);
        WorkProgram::factory()->create(['organization_id' => $organization->id, 'name' => 'Proker Developer Lihat']);
        $developer = User::factory()->create(['role' => User::ROLE_DEVELOPER]);

        $this->actingAs($developer)
            ->get(route('simora.e-progress.proker'))
            ->assertOk()
            ->assertSee('Proker Developer Lihat');

        $this->actingAs($developer)
            ->get(route('simora.e-progress.proker', ['organization' => 'hima-ti']))
            ->assertOk()
            ->assertSee('selected', false);

        $this->actingAs($developer)
            ->post(route('simora.e-progress.proker.store'), [
                'name' => 'Bala',
                'status' => 'perencanaan',
            ])
            ->assertForbidden();
    }

    public function test_eprogress_dashboard_shows_real_counts_from_the_database(): void
    {
        $organization = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]);
        WorkProgram::factory()->count(2)->create(['organization_id' => $organization->id]);

        $this->actingAs($account)
            ->get(route('simora.e-progress'))
            ->assertOk()
            ->assertSee('Program Kerja')
            ->assertSee('>2<', false);
    }
}
