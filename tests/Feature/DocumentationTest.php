<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Documentation;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentationTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_can_upload_documentation(): void
    {
        Storage::fake('public');
        $organization = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]);
        $activity = Activity::factory()->create(['organization_id' => $organization->id, 'name' => 'Seminar']);

        $this->actingAs($account)
            ->post(route('simora.e-progress.dokumentasi.store'), [
                'activity_id' => $activity->id,
                'title' => 'Foto sesi pertama',
                'type' => 'foto',
                'files' => [UploadedFile::fake()->image('foto.jpg')],
            ])
            ->assertRedirect(route('simora.e-progress.dokumentasi', ['activity' => $activity->id]));

        $document = Documentation::where('organization_id', $organization->id)->firstOrFail();

        $this->assertSame('Foto sesi pertama', $document->title);
        $this->assertSame('foto', $document->type);
        $this->assertSame($account->id, $document->uploaded_by);
        Storage::disk('public')->assertExists($document->file_path);

        $this->actingAs($account)
            ->get(route('simora.e-progress.dokumentasi', ['activity' => $activity->id]))
            ->assertOk()
            ->assertSee('Foto sesi pertama');
    }

    public function test_organization_can_upload_multiple_documentation_files(): void
    {
        Storage::fake('public');
        $organization = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]);
        $activity = Activity::factory()->create(['organization_id' => $organization->id]);

        $this->actingAs($account)
            ->post(route('simora.e-progress.dokumentasi.store'), [
                'activity_id' => $activity->id,
                'title' => 'Sesi foto',
                'type' => 'foto',
                'files' => [
                    UploadedFile::fake()->image('satu.jpg'),
                    UploadedFile::fake()->image('dua.jpg'),
                    UploadedFile::fake()->image('tiga.jpg'),
                ],
            ])
            ->assertRedirect();

        $this->assertSame(3, Documentation::where('activity_id', $activity->id)->count());
        $this->assertTrue(Documentation::where('title', 'Sesi foto (1)')->exists());
        $this->assertTrue(Documentation::where('title', 'Sesi foto (3)')->exists());
    }

    public function test_documentation_must_belong_to_own_organization_activity(): void
    {
        Storage::fake('public');
        $own = Organization::factory()->create();
        $other = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $own->id,
        ]);
        $foreignActivity = Activity::factory()->create(['organization_id' => $other->id]);

        $this->actingAs($account)
            ->post(route('simora.e-progress.dokumentasi.store'), [
                'activity_id' => $foreignActivity->id,
                'title' => 'Bala',
                'type' => 'foto',
                'files' => [UploadedFile::fake()->image('foto.jpg')],
            ])
            ->assertSessionHasErrors('activity_id');
    }

    public function test_organization_cannot_manage_another_organizations_documentation(): void
    {
        $own = Organization::factory()->create();
        $other = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $own->id,
        ]);
        $foreign = Documentation::factory()->create(['organization_id' => $other->id, 'title' => 'Asing']);

        $this->actingAs($account)
            ->put(route('simora.e-progress.dokumentasi.update', $foreign), [
                'title' => 'Diubah',
                'type' => 'foto',
            ])
            ->assertForbidden();

        $this->actingAs($account)
            ->delete(route('simora.e-progress.dokumentasi.destroy', $foreign))
            ->assertForbidden();

        $this->assertSame('Asing', $foreign->fresh()->title);
    }

    public function test_developer_cannot_write_documentation_but_can_view_it(): void
    {
        Storage::fake('public');
        $organization = Organization::factory()->create(['slug' => 'hima-ti']);
        $activity = Activity::factory()->create(['organization_id' => $organization->id, 'name' => 'Seminar']);
        Documentation::factory()->create(['organization_id' => $organization->id, 'activity_id' => $activity->id, 'title' => 'Lihat Saja']);
        $developer = User::factory()->create(['role' => User::ROLE_DEVELOPER]);

        $this->actingAs($developer)
            ->get(route('simora.e-progress.dokumentasi', ['organization' => 'hima-ti', 'activity' => $activity->id]))
            ->assertOk()
            ->assertSee('Lihat Saja');

        $this->actingAs($developer)
            ->post(route('simora.e-progress.dokumentasi.store'), [
                'activity_id' => $activity->id,
                'title' => 'Bala',
                'type' => 'foto',
                'files' => [UploadedFile::fake()->image('foto.jpg')],
            ])
            ->assertForbidden();
    }
}
