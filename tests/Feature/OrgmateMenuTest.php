<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Organization;
use App\Models\Position;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrgmateMenuTest extends TestCase
{
    use RefreshDatabase;

    public function test_orgmate_sidebar_has_master_data_menus_without_rbac(): void
    {
        $this->actingAs(User::factory()->create(['role' => User::ROLE_DEVELOPER]));

        $this->get(route('simora.orgmate'))
            ->assertOk()
            // Struktural: judul dashboard + tautan route menu (bukan label rapuh).
            ->assertSee('Dashboard Organisasi')
            ->assertDontSee('Data Organisasi &amp; Periode', false)
            ->assertSee('Struktur Kepengurusan')
            ->assertSee('Data Anggota', false)
            ->assertSee('Rekrutmen Anggota', false)
            ->assertDontSee('Akun, Peran &amp; RBAC', false)
            ->assertSee('Arsip SK &amp; Surat', false)
            ->assertSee('Pengumuman', false)
            ->assertSee(route('simora.orgmate.struktur'), false)
            ->assertSee(route('simora.orgmate.anggota'), false)
            ->assertSee(route('simora.orgmate.rekrutmen'), false)
            ->assertSee(route('simora.orgmate.arsip'), false)
            ->assertSee(route('simora.orgmate.pengumuman'), false);
    }

    public function test_orgmate_pages_show_real_master_data_for_developer(): void
    {
        $organization = Organization::factory()->create([
            'name' => 'HIMA-TI',
            'period' => '2025/2026',
            'registration_url' => 'https://example.com/daftar',
        ]);

        $member = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]);
        $orgMember = Member::factory()->create([
            'organization_id' => $organization->id,
            'email' => 'anggota@hima-ti.example',
        ]);

        $this->actingAs(User::factory()->create(['role' => User::ROLE_DEVELOPER]));

        $this->get(route('simora.orgmate'))
            ->assertOk()
            ->assertSee('2025/2026');

        Position::factory()->create(['organization_id' => $organization->id, 'name' => 'Ketua Umum']);
        Position::factory()->create(['organization_id' => $organization->id, 'name' => 'Sekretaris Umum']);
        Position::factory()->create(['organization_id' => $organization->id, 'name' => 'Bendahara Umum']);

        $this->get(route('simora.orgmate.struktur'))
            ->assertOk()
            ->assertSee('Ketua Umum')
            ->assertSee('Sekretaris Umum')
            ->assertSee('Bendahara Umum');

        $this->get(route('simora.orgmate.anggota'))
            ->assertOk()
            ->assertSee('anggota@hima-ti.example');

        $this->get(route('simora.orgmate.rekrutmen'))
            ->assertOk()
            ->assertSee('https://example.com/daftar', false);

        $this->get(route('simora.orgmate.arsip'))
            ->assertOk()
            ->assertSee('Arsip SK');

        $this->get(route('simora.orgmate.pengumuman'))
            ->assertOk()
            ->assertSee('Pengumuman Internal');
    }

    public function test_organization_role_only_sees_its_own_organization_in_orgmate(): void
    {
        $own = Organization::factory()->create(['name' => 'HIMA-TI', 'period' => '2025/2026']);
        $other = Organization::factory()->create(['name' => 'HIMATRO', 'period' => '2026/2027']);
        $member = User::factory()->create(['role' => User::ROLE_ORGANIZATION, 'organization_id' => $own->id]);
        $ownMember = Member::factory()->create(['organization_id' => $own->id, 'email' => 'anggota@hima-ti.example']);
        Member::factory()->create(['organization_id' => $other->id, 'email' => 'anggota@himatro.example']);

        $this->actingAs($member);

        $this->get(route('simora.orgmate'))
            ->assertOk()
            ->assertSee('2025/2026')
            ->assertDontSee('2026/2027');

        $this->get(route('simora.orgmate.anggota'))
            ->assertOk()
            ->assertSee('anggota@hima-ti.example')
            ->assertDontSee('anggota@himatro.example');

        $this->get(route('simora.orgmate.rekrutmen'))
            ->assertOk()
            ->assertSee('HIMA-TI')
            ->assertDontSee('HIMATRO');
    }

    public function test_organization_role_can_open_orgmate_menus_without_rbac(): void
    {
        $organization = Organization::factory()->create();
        $this->actingAs(User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]));

        foreach ([
            'simora.orgmate', 'simora.orgmate.struktur',
            'simora.orgmate.anggota', 'simora.orgmate.rekrutmen',
            'simora.orgmate.arsip', 'simora.orgmate.pengumuman',
        ] as $routeName) {
            $this->get(route($routeName))->assertOk();
        }

        $this->get('/simora/orgmate/akun')->assertNotFound();
    }

    public function test_rbac_management_stays_in_developer_dashboard(): void
    {
        $this->actingAs(User::factory()->create(['role' => User::ROLE_DEVELOPER]))
            ->get(route('panel'))
            ->assertOk()
            ->assertSee(route('simora.accounts.index'), false);
    }
}
