<?php

namespace Tests\Feature;

use App\Models\Mahasiswa;
use App\Models\Member;
use App\Models\User;
use App\Services\SsoUnujaService;
use App\Services\StudentSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SsoStudentSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_sync_writes_to_mahasiswas_and_never_to_members(): void
    {
        $sso = new class extends SsoUnujaService
        {
            public function authorize(): array
            {
                return [
                    'data_url' => 'https://example.test/data',
                    'prodi' => [
                        ['id' => 'prodi-ti', 'label' => 'Teknologi Informasi'],
                    ],
                    'fakultas' => [['id' => '2', 'label' => 'Teknik']],
                ];
            }

            public function allMahasiswa(string $dataUrl, string $prodiId): array
            {
                return [
                    ['nim' => '2301010001', 'nama' => 'Budi Santoso', 'fakultas' => 'Teknik', 'prodi' => 'Teknologi Informasi', 'id_sms' => 'prodi-ti', 'id_fakultas' => 2],
                    ['nim' => null, 'nama' => null],
                ];
            }
        };

        $results = (new StudentSyncService($sso))->syncAllProdi();

        $this->assertCount(1, $results);
        $this->assertSame(1, $results[0]['created']);
        $this->assertSame(1, $results[0]['skipped']);

        $this->assertDatabaseHas('mahasiswas', [
            'nim' => '2301010001',
            'name' => 'Budi Santoso',
            'study_program' => 'Teknologi Informasi',
        ]);
        $this->assertSame(0, Member::query()->count());
    }

    public function test_sync_updates_existing_mahasiswa_by_nim(): void
    {
        Mahasiswa::factory()->create(['nim' => '2301010001', 'name' => 'Nama Lama']);

        $sso = new class extends SsoUnujaService
        {
            public function authorize(): array
            {
                return [
                    'data_url' => 'https://example.test/data',
                    'prodi' => [['id' => 'prodi-ti', 'label' => 'Teknologi Informasi']],
                    'fakultas' => [['id' => '2', 'label' => 'Teknik']],
                ];
            }

            public function allMahasiswa(string $dataUrl, string $prodiId): array
            {
                return [
                    ['nim' => '2301010001', 'nama' => 'Nama Baru', 'fakultas' => 'Teknik', 'prodi' => 'Teknologi Informasi', 'id_sms' => 'prodi-ti', 'id_fakultas' => 2],
                ];
            }
        };

        $results = (new StudentSyncService($sso))->syncAllProdi();

        $this->assertSame(1, $results[0]['updated']);
        $this->assertSame(1, Mahasiswa::query()->count());

        $this->assertDatabaseHas('mahasiswas', ['nim' => '2301010001', 'name' => 'Nama Baru']);
    }

    public function test_mahasiswa_page_is_developer_only(): void
    {
        $developer = User::factory()->create(['role' => User::ROLE_DEVELOPER]);
        $organizationUser = User::factory()->create(['role' => User::ROLE_ORGANIZATION]);

        $this->actingAs($developer)->get(route('simora.mahasiswa'))->assertOk();

        $organizationUser = User::factory()->create(['role' => User::ROLE_ORGANIZATION]);
        $this->actingAs($organizationUser)->get(route('simora.mahasiswa'))->assertForbidden();
    }
}
