<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanduanTest extends TestCase
{
    use RefreshDatabase;

    public function test_organization_can_open_all_module_guides(): void
    {
        $organization = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]);

        $this->actingAs($account)
            ->get(route('simora.orgmate.panduan'))
            ->assertOk()
            ->assertSee('Panduan ORGMATE')
            ->assertSee('Struktur Kepengurusan')
            ->assertSee('Data Anggota');

        $this->actingAs($account)
            ->get(route('simora.e-progress.panduan'))
            ->assertOk()
            ->assertSee('Panduan E-PROGRESS')
            ->assertSee('Kalender Kegiatan')
            ->assertSee('Absensi / Presensi (QR Code)');

        $this->actingAs($account)
            ->get(route('simora.sentra.panduan'))
            ->assertOk()
            ->assertSee('Panduan SENTRA')
            ->assertSee('Kas Organisasi')
            ->assertSee('Iuran Anggota');
    }

    public function test_dashboard_shows_guide_buttons(): void
    {
        $organization = Organization::factory()->create();
        $account = User::factory()->create([
            'role' => User::ROLE_ORGANIZATION,
            'organization_id' => $organization->id,
        ]);

        $this->actingAs($account)
            ->get(route('simora.e-progress'))
            ->assertOk()
            ->assertSee(route('simora.e-progress.panduan'), false);

        $this->actingAs($account)
            ->get(route('simora.sentra'))
            ->assertOk()
            ->assertSee(route('simora.sentra.panduan'), false);
    }
}
