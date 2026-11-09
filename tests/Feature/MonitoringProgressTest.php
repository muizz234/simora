<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Organization;
use App\Models\User;
use App\Models\WorkProgram;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MonitoringProgressTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_sees_real_progress_from_activities(): void
    {
        $organization = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]);
        $program = WorkProgram::factory()->create(['organization_id' => $organization->id, 'name' => 'Proker StudyBand']);
        Activity::factory()->create(['organization_id' => $organization->id, 'work_program_id' => $program->id, 'status' => 'selesai', 'name' => 'Kegiatan Selesai']);
        Activity::factory()->create(['organization_id' => $organization->id, 'work_program_id' => $program->id, 'status' => 'pelaksanaan', 'name' => 'Kegiatan Jalan']);

        $this->actingAs($account)
            ->get(route('simora.e-progress.monitoring'))
            ->assertOk()
            ->assertSee('Proker StudyBand')
            ->assertSee('1/2 selesai')
            ->assertSee('50%');
    }

    public function test_program_without_activities_shows_zero_progress(): void
    {
        $organization = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]);
        WorkProgram::factory()->create(['organization_id' => $organization->id, 'name' => 'Proker Baru']);

        $this->actingAs($account)
            ->get(route('simora.e-progress.monitoring'))
            ->assertOk()
            ->assertSee('Proker Baru')
            ->assertSee('Belum ada kegiatan');
    }

    public function test_organization_cannot_see_other_organizations_programs(): void
    {
        $own = Organization::factory()->create();
        $other = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $own->id,
        ]);
        WorkProgram::factory()->create(['organization_id' => $other->id, 'name' => 'Proker Asing']);

        $this->actingAs($account)
            ->get(route('simora.e-progress.monitoring'))
            ->assertOk()
            ->assertDontSee('Proker Asing');
    }

    public function test_developer_can_monitor_all_organizations(): void
    {
        $organization = Organization::factory()->create(['slug' => 'hima-ti']);
        WorkProgram::factory()->create(['organization_id' => $organization->id, 'name' => 'Proker Lintas']);
        $developer = User::factory()->create(['role' => User::ROLE_DEVELOPER]);

        $this->actingAs($developer)
            ->get(route('simora.e-progress.monitoring', ['organization' => 'hima-ti']))
            ->assertOk()
            ->assertSee('Proker Lintas');
    }
}
