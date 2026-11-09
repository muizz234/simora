<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Budget;
use App\Models\Organization;
use App\Models\WorkProgram;
use Database\Seeders\ModuleDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_seeder_creates_integrated_data_across_modules(): void
    {
        $organization = Organization::factory()->create(['name' => 'HIMA-TI', 'slug' => 'hima-ti']);

        $this->seed(ModuleDemoSeeder::class);

        $this->assertSame(5, $organization->positions()->count());
        $this->assertSame(1, $organization->workPrograms()->count());
        $this->assertSame(1, $organization->activities()->count());
        $this->assertSame(2, $organization->proposals()->count());
        $this->assertSame(2, $organization->cashTransactions()->count());
        $this->assertSame(1, $organization->budgets()->count());
        $this->assertSame(1, $organization->dues()->count());
    }

    public function test_budget_is_linked_to_eprogress_work_program(): void
    {
        $organization = Organization::factory()->create();
        $program = WorkProgram::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Program Kerja Tahunan',
        ]);
        $budget = Budget::factory()->create([
            'organization_id' => $organization->id,
            'work_program_id' => $program->id,
            'allocated' => 5000000,
            'realized' => 1250000,
        ]);

        $this->assertTrue($budget->workProgram->is($program));
        $this->assertTrue($program->budgets->contains($budget));
        $this->assertSame(25.0, $budget->percentage);
    }

    public function test_activity_is_linked_to_work_program_and_children(): void
    {
        $organization = Organization::factory()->create();
        $program = WorkProgram::factory()->create(['organization_id' => $organization->id]);
        $activity = Activity::factory()->create([
            'organization_id' => $organization->id,
            'work_program_id' => $program->id,
        ]);

        $this->assertTrue($activity->workProgram->is($program));
        $this->assertTrue($program->activities->contains($activity));
        $this->assertSame(0, $activity->committees()->count());
        $this->assertSame(0, $activity->attendances()->count());
    }
}
