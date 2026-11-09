<?php

namespace Tests\Feature;

use App\Models\Archive;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ArchiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_can_upload_an_archive_and_it_appears_in_the_list(): void
    {
        Storage::fake('public');
        $organization = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]);

        $this->actingAs($account)
            ->post(route('simora.orgmate.arsip.store'), [
                'title' => 'SK Kepengurusan',
                'type' => 'sk',
                'number' => '001/SK/2026',
                'date' => '2026-01-15',
                'file' => UploadedFile::fake()->create('sk.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect(route('simora.orgmate.arsip'));

        $archive = Archive::where('organization_id', $organization->id)->firstOrFail();

        $this->assertSame('SK Kepengurusan', $archive->title);
        Storage::disk('public')->assertExists($archive->file_path);

        $this->actingAs($account)
            ->get(route('simora.orgmate.arsip'))
            ->assertOk()
            ->assertSee('SK Kepengurusan')
            ->assertSee($archive->file_url, false);
    }

    public function test_organization_cannot_manage_another_organizations_archive(): void
    {
        $own = Organization::factory()->create();
        $other = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $own->id,
        ]);
        $foreign = Archive::factory()->create(['organization_id' => $other->id, 'title' => 'Arsip Asing']);

        $this->actingAs($account)
            ->put(route('simora.orgmate.arsip.update', $foreign), [
                'title' => 'Diubah',
                'type' => 'sk',
            ])
            ->assertForbidden();

        $this->actingAs($account)
            ->delete(route('simora.orgmate.arsip.destroy', $foreign))
            ->assertForbidden();

        $this->assertSame('Arsip Asing', $foreign->fresh()->title);
    }

    public function test_developer_cannot_write_archives_but_can_view_them(): void
    {
        $organization = Organization::factory()->create();
        Archive::factory()->create(['organization_id' => $organization->id, 'title' => 'Arsip Developer Lihat']);
        $developer = User::factory()->create(['role' => User::ROLE_DEVELOPER]);

        $this->actingAs($developer)
            ->get(route('simora.orgmate.arsip'))
            ->assertOk()
            ->assertSee('Arsip Developer Lihat');

        $this->actingAs($developer)
            ->post(route('simora.orgmate.arsip.store'), [
                'title' => 'Jangan Tersimpan',
                'type' => 'sk',
            ])
            ->assertForbidden();
    }
}
