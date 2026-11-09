<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Evaluation;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EvaluationTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_can_create_evaluation_and_lpj_documents(): void
    {
        $organization = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]);
        $activity = Activity::factory()->create(['organization_id' => $organization->id, 'name' => 'Seminar']);

        $this->actingAs($account)
            ->post(route('simora.e-progress.evaluasi.store'), [
                'activity_id' => $activity->id,
                'title' => 'Evaluasi Pelaksanaan',
                'content' => 'Kegiatan berjalan lancar.',
                'type' => Evaluation::TYPE_EVALUATION,
                'status' => Evaluation::STATUS_DONE,
            ])
            ->assertRedirect(route('simora.e-progress.evaluasi', ['activity' => $activity->id]));

        $this->assertDatabaseHas('evaluations', [
            'organization_id' => $organization->id,
            'activity_id' => $activity->id,
            'title' => 'Evaluasi Pelaksanaan',
            'type' => Evaluation::TYPE_EVALUATION,
            'status' => Evaluation::STATUS_DONE,
            'created_by' => $account->id,
        ]);

        $this->actingAs($account)
            ->get(route('simora.e-progress.evaluasi', ['activity' => $activity->id, 'type' => 'evaluasi']))
            ->assertOk()
            ->assertSee('Evaluasi Pelaksanaan');
    }

    public function test_evaluation_must_belong_to_own_organization_activity(): void
    {
        $own = Organization::factory()->create();
        $other = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $own->id,
        ]);
        $foreignActivity = Activity::factory()->create(['organization_id' => $other->id]);

        $this->actingAs($account)
            ->post(route('simora.e-progress.evaluasi.store'), [
                'activity_id' => $foreignActivity->id,
                'title' => 'Bala',
                'type' => Evaluation::TYPE_REPORT,
                'status' => Evaluation::STATUS_DRAFT,
            ])
            ->assertSessionHasErrors('activity_id');
    }

    public function test_organization_cannot_manage_another_organizations_evaluation(): void
    {
        $own = Organization::factory()->create();
        $other = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $own->id,
        ]);
        $foreign = Evaluation::factory()->create(['organization_id' => $other->id, 'title' => 'Asing']);

        $this->actingAs($account)
            ->put(route('simora.e-progress.evaluasi.update', $foreign), [
                'title' => 'Diubah',
                'type' => Evaluation::TYPE_EVALUATION,
                'status' => Evaluation::STATUS_DRAFT,
            ])
            ->assertForbidden();

        $this->actingAs($account)
            ->delete(route('simora.e-progress.evaluasi.destroy', $foreign))
            ->assertForbidden();

        $this->assertSame('Asing', $foreign->fresh()->title);
    }

    public function test_developer_cannot_write_evaluations_but_can_view_them(): void
    {
        $organization = Organization::factory()->create(['slug' => 'hima-ti']);
        $activity = Activity::factory()->create(['organization_id' => $organization->id, 'name' => 'Seminar']);
        Evaluation::factory()->create(['organization_id' => $organization->id, 'activity_id' => $activity->id, 'title' => 'Lihat Saja']);
        $developer = User::factory()->create(['role' => User::ROLE_DEVELOPER]);

        $this->actingAs($developer)
            ->get(route('simora.e-progress.evaluasi', ['organization' => 'hima-ti', 'activity' => $activity->id]))
            ->assertOk()
            ->assertSee('Lihat Saja');

        $this->actingAs($developer)
            ->post(route('simora.e-progress.evaluasi.store'), [
                'activity_id' => $activity->id,
                'title' => 'Bala',
                'type' => Evaluation::TYPE_EVALUATION,
                'status' => Evaluation::STATUS_DRAFT,
            ])
            ->assertForbidden();
    }
}
