<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_can_publish_an_announcement_and_it_appears_in_the_list(): void
    {
        $organization = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]);

        $this->actingAs($account)
            ->post(route('simora.orgmate.pengumuman.store'), [
                'title' => 'Rapat Internal Bulan Ini',
                'content' => 'Rapat internal pekan depan. Wajib hadir.',
                'published_at' => '2026-11-08',
            ])
            ->assertRedirect(route('simora.orgmate.pengumuman'));

        $this->assertDatabaseHas('announcements', [
            'organization_id' => $organization->id,
            'title' => 'Rapat Internal Bulan Ini',
            'created_by' => $account->id,
        ]);

        $this->actingAs($account)
            ->get(route('simora.orgmate.pengumuman'))
            ->assertOk()
            ->assertSee('Rapat Internal Bulan Ini')
            ->assertSee('Rapat internal pekan depan. Wajib hadir.');
    }

    public function test_organization_cannot_manage_another_organizations_announcement(): void
    {
        $own = Organization::factory()->create();
        $other = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $own->id,
        ]);
        $foreign = Announcement::factory()->create(['organization_id' => $other->id, 'title' => 'Asing']);

        $this->actingAs($account)
            ->put(route('simora.orgmate.pengumuman.update', $foreign), [
                'title' => 'Diubah',
                'content' => 'Isi diubah',
            ])
            ->assertForbidden();

        $this->actingAs($account)
            ->delete(route('simora.orgmate.pengumuman.destroy', $foreign))
            ->assertForbidden();

        $this->assertSame('Asing', $foreign->fresh()->title);
    }

    public function test_developer_cannot_write_announcements_but_can_view_them(): void
    {
        $organization = Organization::factory()->create();
        Announcement::factory()->create(['organization_id' => $organization->id, 'title' => 'Lihat Saja']);
        $developer = User::factory()->create(['role' => User::ROLE_DEVELOPER]);

        $this->actingAs($developer)
            ->get(route('simora.orgmate.pengumuman'))
            ->assertOk()
            ->assertSee('Lihat Saja');

        $this->actingAs($developer)
            ->post(route('simora.orgmate.pengumuman.store'), [
                'title' => 'Bala',
                'content' => 'Isi',
            ])
            ->assertForbidden();
    }
}
