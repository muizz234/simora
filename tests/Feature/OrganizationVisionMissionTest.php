<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationVisionMissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_can_save_vision_and_mission_from_its_panel(): void
    {
        $organization = Organization::factory()->create([
            'name' => 'HIMA-TI',
            'vision' => null,
            'mission' => null,
        ]);
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]);

        $this->actingAs($account)
            ->put(route('simora.my-vision-mission.update'), [
                'vision' => 'Menjadi himpunan yang unggul dan inovatif.',
                'mission' => "Menjalankan program kerja tahunan.\nMembangun kolaborasi antar anggota.",
            ])
            ->assertRedirect(route('panel'));

        $this->assertSame('Menjadi himpunan yang unggul dan inovatif.', $organization->fresh()->vision);
        $this->assertSame("Menjalankan program kerja tahunan.\nMembangun kolaborasi antar anggota.", $organization->fresh()->mission);
    }

    public function test_saved_vision_and_mission_appear_on_public_landing_and_structure_page(): void
    {
        $organization = Organization::factory()->create([
            'name' => 'HIMA-TI',
            'vision' => 'Visi contoh dari organisasi.',
            'mission' => 'Misi contoh dari organisasi.',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Visi contoh dari organisasi.')
            ->assertSee('Misi contoh dari organisasi.');

        $this->get(route('struktur.show', $organization))
            ->assertOk()
            ->assertSee('Visi contoh dari organisasi.')
            ->assertSee('Misi contoh dari organisasi.');
    }

    public function test_organization_cannot_update_another_organizations_vision_and_mission(): void
    {
        $own = Organization::factory()->create(['vision' => 'Visi A', 'mission' => 'Misi A']);
        $other = Organization::factory()->create(['vision' => 'Visi B', 'mission' => 'Misi B']);
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $own->id,
        ]);

        $this->actingAs($account)
            ->put(route('simora.my-vision-mission.update'), [
                'vision' => 'Visi baru A',
                'mission' => 'Misi baru A',
            ])
            ->assertRedirect(route('panel'));

        $this->assertSame('Visi baru A', $own->fresh()->vision);
        $this->assertSame('Visi B', $other->fresh()->vision);
        $this->assertSame('Misi B', $other->fresh()->mission);
    }

    public function test_developer_cannot_use_the_organization_vision_mission_form(): void
    {
        $this->actingAs(User::factory()->create(['role' => User::ROLE_DEVELOPER]));

        $this->put(route('simora.my-vision-mission.update'), [
            'vision' => 'Visi developer',
            'mission' => 'Misi developer',
        ])->assertForbidden();
    }

    public function test_panel_shows_vision_mission_form_for_organization_accounts(): void
    {
        $organization = Organization::factory()->create(['vision' => 'Visi tersimpan', 'mission' => 'Misi tersimpan']);
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]);

        $this->actingAs($account)
            ->get(route('panel'))
            ->assertOk()
            ->assertSee('Visi &amp; Misi Organisasi', false)
            ->assertSee('Visi tersimpan')
            ->assertSee('Misi tersimpan');
    }
}
