<?php

namespace Tests\Feature;

use App\Models\Budget;
use App\Models\Organization;
use App\Models\User;
use App\Models\WorkProgram;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BudgetTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_can_add_budget_and_percentage_is_computed(): void
    {
        $organization = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]);
        $program = WorkProgram::factory()->create(['organization_id' => $organization->id, 'name' => 'Proker StudyBand']);

        $this->actingAs($account)
            ->post(route('simora.sentra.rab.store'), [
                'work_program_id' => $program->id,
                'program_name' => 'Seminar Nasional',
                'year' => now()->format('Y'),
                'allocated' => 5000000,
                'realized' => 2500000,
            ])
            ->assertRedirect(route('simora.sentra.rab', ['periode' => now()->format('Y')]));

        $budget = Budget::where('organization_id', $organization->id)->firstOrFail();

        $this->assertSame('Seminar Nasional', $budget->program_name);
        $this->assertSame(50.0, $budget->percentage);

        $this->actingAs($account)
            ->get(route('simora.sentra.rab', ['periode' => now()->format('Y')]))
            ->assertOk()
            ->assertSee('Seminar Nasional')
            ->assertSee('50%');
    }

    public function test_foreign_work_program_is_rejected(): void
    {
        $own = Organization::factory()->create();
        $other = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $own->id,
        ]);
        $foreignProgram = WorkProgram::factory()->create(['organization_id' => $other->id]);

        $this->actingAs($account)
            ->post(route('simora.sentra.rab.store'), [
                'work_program_id' => $foreignProgram->id,
                'program_name' => 'Bala',
                'year' => now()->format('Y'),
                'allocated' => 1000000,
                'realized' => 0,
            ])
            ->assertSessionHasErrors('work_program_id');
    }

    public function test_organization_cannot_manage_another_organizations_budget(): void
    {
        $own = Organization::factory()->create();
        $other = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $own->id,
        ]);
        $foreign = Budget::factory()->create(['organization_id' => $other->id, 'program_name' => 'Asing']);

        $this->actingAs($account)
            ->put(route('simora.sentra.rab.update', $foreign), [
                'program_name' => 'Diubah',
                'year' => now()->format('Y'),
                'allocated' => 100,
                'realized' => 0,
            ])
            ->assertForbidden();

        $this->actingAs($account)
            ->delete(route('simora.sentra.rab.destroy', $foreign))
            ->assertForbidden();

        $this->assertSame('Asing', $foreign->fresh()->program_name);
    }

    public function test_developer_cannot_write_budget_but_can_view_it(): void
    {
        $organization = Organization::factory()->create(['slug' => 'hima-ti']);
        Budget::factory()->create(['organization_id' => $organization->id, 'program_name' => 'Lihat Saja', 'year' => now()->format('Y')]);
        $developer = User::factory()->create(['role' => User::ROLE_DEVELOPER]);

        $this->actingAs($developer)
            ->get(route('simora.sentra.rab', ['organization' => 'hima-ti', 'periode' => now()->format('Y')]))
            ->assertOk()
            ->assertSee('Lihat Saja');

        $this->actingAs($developer)
            ->post(route('simora.sentra.rab.store'), [
                'program_name' => 'Bala',
                'year' => now()->format('Y'),
                'allocated' => 100,
                'realized' => 0,
            ])
            ->assertForbidden();
    }
}
