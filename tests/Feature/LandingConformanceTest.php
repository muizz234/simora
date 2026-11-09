<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\Documentation;
use App\Models\Organization;
use App\Models\WorkProgram;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingConformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_activity_with_documentation_appears_on_landing_with_proker_and_organizer(): void
    {
        $organization = Organization::factory()->create(['name' => 'HIMA-TI FT']);
        $program = WorkProgram::factory()->create(['organization_id' => $organization->id, 'name' => 'Proker StudyBand']);
        $activity = Activity::factory()->create([
            'organization_id' => $organization->id,
            'work_program_id' => $program->id,
            'name' => 'Studium Generale',
            'location' => 'Aula FT',
            'start_at' => '2026-09-18 09:00:00',
        ]);
        Documentation::factory()->create([
            'organization_id' => $organization->id,
            'activity_id' => $activity->id,
            'title' => 'Foto sesi',
            'type' => 'foto',
            'file_path' => 'documentation/foto.jpg',
        ]);

        $this->get(route('kegiatan.index'))
            ->assertOk()
            ->assertSee('Kabar')
            ->assertSee('Studium Generale')
            ->assertSee('Proker StudyBand')
            ->assertSee('HIMA-TI FT')
            ->assertSee('Jumat, 18 September 2026')
            ->assertSee('Aula FT')
            ->assertSee('Lihat Selengkapnya')
            ->assertSee(route('kegiatan.show', $activity), false);

        $this->get(route('kegiatan.show', $activity))
            ->assertOk()
            ->assertSee('Studium Generale')
            ->assertSee('Diselenggarakan oleh HIMA-TI FT')
            ->assertSee('Proker: Proker StudyBand')
            ->assertSee('Galeri Dokumentasi')
            ->assertSee('Foto sesi');

        // Kegiatan tidak tampil di landing utama — hanya lewat menu Kegiatan.
        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Studium Generale');
    }

    public function test_activity_without_documentation_does_not_appear_on_landing_kegiatan(): void
    {
        $organization = Organization::factory()->create();
        Activity::factory()->create([
            'organization_id' => $organization->id,
            'name' => 'Kegiatan Tanpa Dokumen',
        ]);

        $this->get(route('kegiatan.index'))
            ->assertOk()
            ->assertDontSee('Kegiatan Tanpa Dokumen');
    }

    public function test_landing_is_read_only_with_simora_sso_button_and_dynamic_organizations(): void
    {
        Organization::factory()->create([
            'name' => 'HIMA-TI',
            'full_name' => 'Himpunan Mahasiswa Teknologi Informasi',
            'description' => 'Deskripsi dinamis untuk landing.',
            'vision' => 'Visi dinamis.',
            'mission' => 'Misi dinamis.',
            'period' => '2025/2026',
            'registration_url' => 'https://example.com/daftar-hima-ti',
            'banner_color' => '#171717',
        ]);

        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('SIMORA', false)
            ->assertSee(route('login'), false)
            ->assertSee('Login SSO SIMORA', false)
            ->assertSee('HIMA-TI')
            ->assertSee('Himpunan Mahasiswa Teknologi Informasi')
            ->assertSee('Deskripsi dinamis untuk landing.')
            ->assertSee('Visi dinamis.')
            ->assertSee('Misi dinamis.')
            ->assertSee('PERIODE · 2025/2026')
            ->assertDontSee('KABAR')
            ->assertDontSee('Kalender Kegiatan Terbuka')
            ->assertSee('GABUNG', false)
            ->assertSee('https://example.com/daftar-hima-ti', false)
            ->assertSee('Link Pendaftaran', false)
            ->assertDontSee('method="POST"', false);
    }
}
