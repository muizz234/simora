<?php

namespace Tests\Feature;

use App\Models\Mahasiswa;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PositionTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_can_add_a_position_and_it_appears_on_struktur_page(): void
    {
        $organization = Organization::factory()->create(['name' => 'HIMA-TI']);
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]);
        $member = Member::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Budi Santoso',
        ]);

        $this->actingAs($account)
            ->post(route('simora.orgmate.struktur.store'), [
                'name' => 'Ketua Umum',
                'member_id' => $member->id,
                'sort_order' => 1,
            ])
            ->assertRedirect(route('simora.orgmate.struktur'));

        $this->assertDatabaseHas('positions', [
            'organization_id' => $organization->id,
            'name' => 'Ketua Umum',
            'member_id' => $member->id,
        ]);

        $this->actingAs($account)
            ->get(route('simora.orgmate.struktur'))
            ->assertOk()
            ->assertSee('Ketua Umum')
            ->assertSee('Budi Santoso');
    }

    public function test_organization_cannot_manage_another_organizations_position(): void
    {
        $own = Organization::factory()->create();
        $other = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $own->id,
        ]);
        $foreign = Position::factory()->create(['organization_id' => $other->id, 'name' => 'Ketua Umum']);

        $this->actingAs($account)
            ->put(route('simora.orgmate.struktur.update', $foreign), [
                'name' => 'Diubah',
            ])
            ->assertForbidden();

        $this->actingAs($account)
            ->delete(route('simora.orgmate.struktur.destroy', $foreign))
            ->assertForbidden();

        $this->assertSame('Ketua Umum', $foreign->fresh()->name);
    }

    public function test_developer_cannot_write_positions_but_can_view_them(): void
    {
        $organization = Organization::factory()->create(['name' => 'HIMA-TI']);
        Position::factory()->create(['organization_id' => $organization->id, 'name' => 'Bendahara Umum']);
        $developer = User::factory()->create(['role' => User::ROLE_DEVELOPER]);

        $this->actingAs($developer)
            ->get(route('simora.orgmate.struktur'))
            ->assertOk()
            ->assertSee('Bendahara Umum');

        $this->actingAs($developer)
            ->post(route('simora.orgmate.struktur.store'), ['name' => 'Jabatan Bala'])
            ->assertForbidden();
    }

    public function test_organization_can_pick_holder_by_mahasiswa_id_of_own_prodi_grouped_by_angkatan(): void
    {
        $organization = Organization::factory()->create(['name' => 'HIMA-TI', 'focus' => 'Teknologi Informasi']);
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
            ->get(route('simora.orgmate.struktur'))
            ->assertOk()
            ->assertSee('Angkatan 2023')
            ->assertSee('Budi Santoso — 2301010001');

        $this->actingAs($account)
            ->post(route('simora.orgmate.struktur.store'), [
                'name' => 'Ketua Umum',
                'mahasiswa_id' => $mahasiswa->id,
                'sort_order' => 1,
            ])
            ->assertRedirect(route('simora.orgmate.struktur'));

        $position = Position::query()->where('organization_id', $organization->id)->firstOrFail();

        $this->assertNotNull($position->member_id);
        $this->assertDatabaseHas('members', [
            'id' => $position->member_id,
            'organization_id' => $organization->id,
            'nim' => '2301010001',
            'name' => 'Budi Santoso',
            'study_program' => 'Teknologi Informasi',
        ]);
    }

    public function test_organization_cannot_pick_mahasiswa_of_another_prodi(): void
    {
        $organization = Organization::factory()->create(['focus' => 'Teknologi Informasi']);
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]);
        $otherProdi = Mahasiswa::factory()->create(['study_program' => 'Teknik Informatika']);

        $this->actingAs($account)
            ->post(route('simora.orgmate.struktur.store'), [
                'name' => 'Ketua Umum',
                'mahasiswa_id' => $otherProdi->id,
            ])
            ->assertSessionHasErrors('mahasiswa_id');
    }

    public function test_holder_must_be_a_member_of_the_same_organization(): void
    {
        $organization = Organization::factory()->create();
        $other = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]);
        $outsider = Member::factory()->create(['organization_id' => $other->id]);

        $this->actingAs($account)
            ->post(route('simora.orgmate.struktur.store'), [
                'name' => 'Ketua Umum',
                'member_id' => $outsider->id,
            ])
            ->assertSessionHasErrors('member_id');
    }
}
