<?php

namespace Tests\Feature;

use App\Models\Mahasiswa;
use App\Models\Member;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MemberTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_can_add_a_member_with_nim_and_it_appears_in_the_list(): void
    {
        $organization = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]);

        $this->actingAs($account)
            ->post(route('simora.orgmate.anggota.store'), [
                'name' => 'Budi Santoso',
                'nim' => '2301010001',
                'email' => 'budi@example.com',
                'phone' => '081234567890',
                'faculty' => 'Teknologi Informasi',
            ])
            ->assertRedirect(route('simora.orgmate.anggota'));

        $this->assertDatabaseHas('members', [
            'name' => 'Budi Santoso',
            'nim' => '2301010001',
            'organization_id' => $organization->id,
        ]);
        // Anggota bukan akun login.
        $this->assertDatabaseMissing('users', ['name' => 'Budi Santoso']);

        $this->actingAs($account)
            ->get(route('simora.orgmate.anggota'))
            ->assertOk()
            ->assertSee('Budi Santoso')
            ->assertSee('2301010001');
    }

    public function test_organization_cannot_manage_another_organizations_member(): void
    {
        $own = Organization::factory()->create();
        $other = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $own->id,
        ]);
        $foreign = Member::factory()->create(['organization_id' => $other->id]);

        $this->actingAs($account)
            ->put(route('simora.orgmate.anggota.update', $foreign), [
                'name' => 'Diubah',
            ])
            ->assertForbidden();

        $this->actingAs($account)
            ->delete(route('simora.orgmate.anggota.destroy', $foreign))
            ->assertForbidden();

        $this->assertNotNull($foreign->fresh());
    }

    public function test_developer_cannot_write_members_but_can_view_them(): void
    {
        $organization = Organization::factory()->create();
        Member::factory()->create([
            'name' => 'Anggota Contoh',
            'organization_id' => $organization->id,
        ]);
        $developer = User::factory()->create(['role' => User::ROLE_DEVELOPER]);

        $this->actingAs($developer)
            ->get(route('simora.orgmate.anggota'))
            ->assertOk()
            ->assertSee('Anggota Contoh');

        $this->actingAs($developer)
            ->post(route('simora.orgmate.anggota.store'), [
                'name' => 'Bala',
            ])
            ->assertForbidden();
    }

    public function test_organization_can_upload_a_member_photo_and_it_replaces_the_initials(): void
    {
        Storage::fake('public');
        $organization = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]);

        $this->actingAs($account)
            ->post(route('simora.orgmate.anggota.store'), [
                'name' => 'Budi Santoso',
                'nim' => '2301010001',
                'avatar' => UploadedFile::fake()->image('budi.jpg'),
            ])
            ->assertRedirect(route('simora.orgmate.anggota'));

        $member = Member::where('organization_id', $organization->id)->firstOrFail();

        $this->assertNotNull($member->avatar_path);
        Storage::disk('public')->assertExists($member->avatar_path);
        $this->assertStringStartsWith(Storage::disk('public')->url('avatars/'), $member->avatar_url);

        $this->actingAs($account)
            ->get(route('simora.orgmate.anggota'))
            ->assertOk()
            ->assertSee($member->avatar_url, false);
    }

    public function test_member_list_pagination_shows_info_and_page_links(): void
    {
        $organization = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]);
        Member::factory()->count(11)->create([
            'organization_id' => $organization->id,
            'status' => 'aktif',
        ]);

        $this->actingAs($account)
            ->get(route('simora.orgmate.anggota'))
            ->assertOk()
            ->assertSee('Menampilkan 1–10 dari 11 anggota')
            ->assertSee('Berikutnya ›')
            ->assertSee(route('simora.orgmate.anggota', ['page' => 2]), false);
    }

    public function test_member_nim_must_be_unique_within_an_organization_but_may_repeat_across_organizations(): void
    {
        $organization = Organization::factory()->create();
        $other = Organization::factory()->create();
        Member::factory()->create([
            'organization_id' => $organization->id,
            'nim' => '2301010001',
        ]);
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]);

        $this->actingAs($account)
            ->post(route('simora.orgmate.anggota.store'), [
                'name' => 'Duplikat NIM',
                'nim' => '2301010001',
            ])
            ->assertSessionHasErrors('nim');

        // NIM yang sama di organisasi berbeda boleh.
        $otherAccount = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $other->id,
        ]);

        $this->actingAs($otherAccount)
            ->post(route('simora.orgmate.anggota.store'), [
                'name' => 'NIM Sama Organisasi Lain',
                'nim' => '2301010001',
            ])
            ->assertRedirect(route('simora.orgmate.anggota'));
    }

    public function test_organization_adds_member_by_picking_mahasiswa_of_own_prodi_grouped_by_angkatan(): void
    {
        $organization = Organization::factory()->create(['focus' => 'Teknologi Informasi']);
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]);
        $mahasiswa = Mahasiswa::factory()->create([
            'nim' => '2301010001',
            'name' => 'Budi Santoso',
            'study_program' => 'Teknologi Informasi',
            'enrollment_year' => '20231',
        ]);

        $this->actingAs($account)
            ->get(route('simora.orgmate.anggota'))
            ->assertOk()
            ->assertSee('Angkatan 2023')
            ->assertSee('Budi Santoso — 2301010001');

        $this->actingAs($account)
            ->post(route('simora.orgmate.anggota.store'), [
                'mahasiswa_id' => $mahasiswa->id,
            ])
            ->assertRedirect(route('simora.orgmate.anggota'));

        $this->assertDatabaseHas('members', [
            'organization_id' => $organization->id,
            'nim' => '2301010001',
            'name' => 'Budi Santoso',
            'study_program' => 'Teknologi Informasi',
        ]);
    }

    public function test_organization_cannot_add_member_from_another_prodi_mahasiswa(): void
    {
        $organization = Organization::factory()->create(['focus' => 'Teknologi Informasi']);
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]);
        $otherProdi = Mahasiswa::factory()->create(['study_program' => 'Teknik Informatika']);

        $this->actingAs($account)
            ->post(route('simora.orgmate.anggota.store'), [
                'mahasiswa_id' => $otherProdi->id,
            ])
            ->assertSessionHasErrors('mahasiswa_id');
    }
}
