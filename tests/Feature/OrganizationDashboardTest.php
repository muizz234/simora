<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrganizationDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_user_sees_organization_dashboard_with_linked_organization_data(): void
    {
        $organization = Organization::factory()->create([
            'name' => 'HIMA-TI',
            'full_name' => 'Himpunan Mahasiswa Teknologi Informasi',
            'focus' => 'Teknologi Informasi',
            'period' => '2025/2026',
            'description' => 'Deskripsi HIMA-TI untuk pengujian.',
            'vision' => 'Visi HIMA-TI.',
            'mission' => 'Misi HIMA-TI.',
            'banner_color' => '#171717',
        ]);

        $account = User::factory()->create([
            'name' => 'HIMA-TI',
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]);

        User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]);

        $response = $this->actingAs($account)->get(route('panel'));
        $response->assertOk()
            // Struktural: status + data organisasi dari DB + route modul.
            ->assertSee('Dashboard Organisasi')
            ->assertSee('HIMA-TI')
            ->assertSee('Himpunan Mahasiswa Teknologi Informasi')
            ->assertSee('Teknologi Informasi')
            ->assertSee('2025/2026')
            ->assertSee('Deskripsi HIMA-TI untuk pengujian.')
            ->assertSee('Visi HIMA-TI.')
            ->assertSee('Misi HIMA-TI.')
            ->assertSee('ORGMATE')
            ->assertSee(route('simora.orgmate'), false)
            ->assertSee('E-PROGRESS')
            ->assertSee(route('simora.e-progress'), false)
            ->assertSee('SENTRA')
            ->assertSee(route('simora.sentra'), false);
        // Judul deskriptif kartu modul boleh berubah; route adalah kontrak stabil.
    }

    public function test_developer_still_sees_developer_dashboard(): void
    {
        $this->actingAs(User::factory()->create(['role' => User::ROLE_DEVELOPER]))
            ->get(route('panel'))
            ->assertOk()
            ->assertSee('PANEL PENGEMBANG')
            ->assertSee('Kelola landing page dan pengembangan SIMORA.');
    }

    public function test_organization_without_linked_organization_gets_guidance(): void
    {
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => null,
        ]);

        $this->actingAs($account)
            ->get(route('panel'))
            ->assertOk()
            ->assertSee('Akun ini belum terhubung ke organisasi.');
    }
}
