<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Budget;
use App\Models\CashTransaction;
use App\Models\Dues;
use App\Models\Evaluation;
use App\Models\Member;
use App\Models\Organization;
use App\Models\WorkProgram;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternalDirectoryApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['microservices.token' => 'test-service-token']);
    }

    public function test_internal_endpoints_require_a_valid_service_token(): void
    {
        $organization = Organization::factory()->create();

        $this->getJson("/api/internal/v1/organizations/{$organization->id}/members")
            ->assertUnauthorized();

        $this->withHeader('X-Service-Token', 'wrong-token')
            ->getJson("/api/internal/v1/organizations/{$organization->id}/members")
            ->assertUnauthorized();
    }

    public function test_members_endpoint_returns_only_minimal_member_data(): void
    {
        $organization = Organization::factory()->create();
        $member = Member::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Siti Aminah',
        ]);
        Member::factory()->create();

        $response = $this->withHeader('X-Service-Token', 'test-service-token')
            ->getJson("/api/internal/v1/organizations/{$organization->id}/members")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['id' => $member->id, 'name' => 'Siti Aminah']);

        $row = $response->json('data.0');

        $this->assertArrayNotHasKey('nim', $row);
        $this->assertArrayNotHasKey('email', $row);
        $this->assertArrayNotHasKey('phone', $row);
    }

    public function test_work_programs_endpoint_returns_organization_programs(): void
    {
        $organization = Organization::factory()->create();
        $program = WorkProgram::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Proker Unggulan',
        ]);

        $this->withToken('test-service-token')
            ->getJson("/api/internal/v1/organizations/{$organization->id}/work-programs")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['id' => $program->id, 'name' => 'Proker Unggulan']);
    }

    public function test_lpjs_endpoint_returns_lpj_with_activity_context(): void
    {
        $organization = Organization::factory()->create();
        $program = WorkProgram::factory()->create(['organization_id' => $organization->id]);
        $activity = Activity::factory()->create([
            'organization_id' => $organization->id,
            'work_program_id' => $program->id,
            'name' => 'Seminar Teknologi',
        ]);
        $lpj = Evaluation::factory()->create([
            'organization_id' => $organization->id,
            'activity_id' => $activity->id,
            'type' => Evaluation::TYPE_REPORT,
            'title' => 'LPJ Seminar Teknologi',
        ]);
        Evaluation::factory()->create([
            'organization_id' => $organization->id,
            'activity_id' => $activity->id,
            'type' => Evaluation::TYPE_EVALUATION,
        ]);

        $this->withToken('test-service-token')
            ->getJson("/api/internal/v1/organizations/{$organization->id}/lpjs")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'id' => $lpj->id,
                'title' => 'LPJ Seminar Teknologi',
                'work_program_id' => $program->id,
            ])
            ->assertJsonFragment([
                'activity' => ['id' => $activity->id, 'name' => 'Seminar Teknologi'],
            ]);
    }

    public function test_budgets_endpoint_returns_only_organization_budgets(): void
    {
        $organization = Organization::factory()->create();
        $budget = Budget::factory()->create([
            'organization_id' => $organization->id,
            'program_name' => 'RAB Seminar',
            'year' => 2026,
            'allocated' => 1500000,
            'realized' => 450000,
        ]);
        Budget::factory()->create();

        $response = $this->withToken('test-service-token')
            ->getJson("/api/internal/v1/organizations/{$organization->id}/budgets")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['id' => $budget->id, 'program_name' => 'RAB Seminar']);

        $row = $response->json('data.0');

        $this->assertArrayNotHasKey('organization_id', $row);
    }

    public function test_dues_endpoint_returns_organization_dues_without_proof_path(): void
    {
        $organization = Organization::factory()->create();
        $due = Dues::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Siti Aminah',
            'amount' => 50000,
            'status' => Dues::STATUS_PAID,
            'period' => 'November 2026',
        ]);
        Dues::factory()->create();

        $response = $this->withToken('test-service-token')
            ->getJson("/api/internal/v1/organizations/{$organization->id}/dues")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['id' => $due->id, 'name' => 'Siti Aminah']);

        $row = $response->json('data.0');

        $this->assertArrayNotHasKey('proof_path', $row);
        $this->assertArrayNotHasKey('organization_id', $row);
    }

    public function test_cash_transactions_endpoint_returns_only_organization_transactions(): void
    {
        $organization = Organization::factory()->create();
        $transaction = CashTransaction::factory()->create([
            'organization_id' => $organization->id,
            'title' => 'Kuitansi Seminar',
            'type' => CashTransaction::TYPE_EXPENSE,
            'amount' => 250000,
            'date' => '2026-11-01',
            'category' => 'Kegiatan',
        ]);
        CashTransaction::factory()->create();

        $response = $this->withToken('test-service-token')
            ->getJson("/api/internal/v1/organizations/{$organization->id}/cash-transactions")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['id' => $transaction->id, 'title' => 'Kuitansi Seminar']);

        $row = $response->json('data.0');

        $this->assertArrayNotHasKey('proof_path', $row);
        $this->assertArrayNotHasKey('created_by', $row);
        $this->assertArrayNotHasKey('organization_id', $row);
    }
}
