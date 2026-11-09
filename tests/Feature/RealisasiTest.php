<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Budget;
use App\Models\CashTransaction;
use App\Models\Evaluation;
use App\Models\Organization;
use App\Models\User;
use App\Models\WorkProgram;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RealisasiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_recording_realization_increases_budget_and_cash_expense(): void
    {
        $organization = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]);
        $budget = Budget::factory()->create([
            'organization_id' => $organization->id,
            'allocated' => 5000000,
            'realized' => 1000000,
            'year' => now()->format('Y'),
        ]);

        $this->actingAs($account)
            ->post(route('simora.sentra.realisasi.store'), [
                'budget_id' => $budget->id,
                'amount' => 500000,
                'date' => now()->format('Y-m-d'),
                'description' => 'Sewa ruangan',
                'proof' => UploadedFile::fake()->image('kuitansi.jpg'),
            ])
            ->assertRedirect(route('simora.sentra.realisasi', ['periode' => $budget->year]));

        $this->assertEqualsWithDelta(1500000, (float) $budget->fresh()->realized, 0.01);

        $this->assertDatabaseHas('cash_transactions', [
            'organization_id' => $organization->id,
            'type' => CashTransaction::TYPE_EXPENSE,
            'title' => 'Sewa ruangan',
            'amount' => 500000,
            'category' => 'Realisasi Anggaran',
        ]);

        $this->actingAs($account)
            ->get(route('simora.sentra.realisasi', ['periode' => $budget->year]))
            ->assertOk();
    }

    public function test_lpj_documents_from_eprogress_appear_on_realization_page(): void
    {
        $organization = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]);
        $program = WorkProgram::factory()->create(['organization_id' => $organization->id, 'name' => 'Proker StudyBand']);
        Budget::factory()->create([
            'organization_id' => $organization->id,
            'work_program_id' => $program->id,
            'program_name' => 'Seminar Nasional',
            'year' => now()->format('Y'),
        ]);
        $activity = Activity::factory()->create(['organization_id' => $organization->id, 'work_program_id' => $program->id]);
        Evaluation::factory()->create([
            'organization_id' => $organization->id,
            'activity_id' => $activity->id,
            'type' => Evaluation::TYPE_REPORT,
            'title' => 'LPJ StudyBand',
        ]);

        $this->actingAs($account)
            ->get(route('simora.sentra.realisasi', ['periode' => now()->format('Y')]))
            ->assertOk()
            ->assertSee('LPJ StudyBand')
            ->assertSee('Dokumen LPJ dari E-Progress');
    }

    public function test_organization_cannot_realize_another_organizations_budget(): void
    {
        $own = Organization::factory()->create();
        $other = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $own->id,
        ]);
        $foreignBudget = Budget::factory()->create(['organization_id' => $other->id]);

        $this->actingAs($account)
            ->post(route('simora.sentra.realisasi.store'), [
                'budget_id' => $foreignBudget->id,
                'amount' => 100000,
                'date' => now()->format('Y-m-d'),
                'proof' => UploadedFile::fake()->image('kuitansi.jpg'),
            ])
            ->assertForbidden();

        $this->assertSame(0, CashTransaction::where('organization_id', $other->id)->count());
    }

    public function test_proof_is_required_for_realization(): void
    {
        $organization = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]);
        $budget = Budget::factory()->create(['organization_id' => $organization->id]);

        $this->actingAs($account)
            ->post(route('simora.sentra.realisasi.store'), [
                'budget_id' => $budget->id,
                'amount' => 100000,
                'date' => now()->format('Y-m-d'),
            ])
            ->assertSessionHasErrors('proof');
    }
}
