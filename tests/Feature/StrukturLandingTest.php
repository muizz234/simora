<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Organization;
use App\Models\Position;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StrukturLandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_struktural_index_shows_logo_cards_and_links_to_detail(): void
    {
        Organization::factory()->create(['name' => 'HIMA-TI', 'slug' => 'hima-ti', 'logo_path' => 'assets/himati.jpg']);
        Organization::factory()->create(['name' => 'HIMATIKA', 'slug' => 'himatika', 'logo_path' => 'assets/himatika.jpg']);

        $this->get(route('struktur.index'))
            ->assertOk()
            ->assertSee('Struktural')
            ->assertSee('HIMA-TI')
            ->assertSee('HIMATIKA')
            ->assertSee(route('struktur.show', Organization::where('slug', 'hima-ti')->first()), false);
    }

    public function test_struktur_detail_shows_full_structure_from_orgmate_data(): void
    {
        $organization = Organization::factory()->create([
            'name' => 'HIMATRO',
            'slug' => 'himatro',
            'full_name' => 'Himpunan Mahasiswa Teknik Elektro',
            'description' => 'Deskripsi struktur.',
            'vision' => 'Visi struktur.',
            'mission' => 'Misi struktur.',
            'period' => '2026/2027',
        ]);

        $member = Member::factory()->create([
            'name' => 'Anggota HIMATRO',
            'organization_id' => $organization->id,
        ]);

        Position::factory()->create(['organization_id' => $organization->id, 'member_id' => $member->id, 'name' => 'Ketua Umum']);
        Position::factory()->create(['organization_id' => $organization->id, 'name' => 'Wakil Ketua Umum']);
        Position::factory()->create(['organization_id' => $organization->id, 'name' => 'Sekretaris Umum']);
        Position::factory()->create(['organization_id' => $organization->id, 'name' => 'Bendahara Umum']);
        Position::factory()->create(['organization_id' => $organization->id, 'name' => 'Koordinator Divisi Humas']);
        Member::factory()->create([
            'name' => 'Anggota Humas',
            'division' => 'Humas',
            'organization_id' => $organization->id,
        ]);

        $this->get(route('struktur.show', $organization))
            ->assertOk()
            ->assertSee('HIMATRO')
            ->assertSee('Himpunan Mahasiswa Teknik Elektro')
            ->assertSee('Ketua Umum')
            ->assertSee('Wakil Ketua Umum')
            ->assertSee('Sekretaris Umum')
            ->assertSee('Bendahara Umum')
            ->assertSee('Anggota Divisi')
            ->assertSee('Anggota Humas')
            ->assertSee('Visi struktur.')
            ->assertSee('Misi struktur.')
            ->assertSee('2026/2027')
            ->assertSee('Anggota HIMATRO');
    }

    public function test_landing_cards_link_to_struktur_detail(): void
    {
        $organization = Organization::factory()->create(['name' => 'HIMA-TI', 'slug' => 'hima-ti']);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee(route('struktur.index'), false)
            ->assertSee(route('struktur.show', $organization), false);
    }
}
