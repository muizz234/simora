<?php

namespace Tests\Unit;

use App\Models\Mahasiswa;
use App\Models\Member;
use App\Services\SsoUnujaService;
use App\Services\StudentSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentSyncServiceTest extends TestCase
{
    use RefreshDatabase;

    private function fakeSso(array $rowsByProdi, array $prodiList): SsoUnujaService
    {
        return new class($rowsByProdi, $prodiList) extends SsoUnujaService
        {
            public function __construct(private array $rowsByProdi, private array $prodiList) {}

            public function authorize(): array
            {
                return [
                    'data_url' => 'https://example.test/data',
                    'prodi' => $this->prodiList,
                    'fakultas' => [],
                ];
            }

            public function allMahasiswa(string $dataUrl, string $prodiId): array
            {
                return $this->rowsByProdi[$prodiId] ?? [];
            }
        };
    }

    public function test_sync_prodi_creates_updates_and_skips(): void
    {
        Mahasiswa::factory()->create(['nim' => '2301010002', 'name' => 'Lama']);

        $sso = $this->fakeSso([
            'prodi-ti' => [
                ['nim' => '2301010001', 'nama' => 'Baru', 'fakultas' => 'Teknik', 'prodi' => 'TI', 'id_sms' => 'prodi-ti', 'id_fakultas' => 2],
                ['nim' => '2301010002', 'nama' => 'Baru Dua', 'fakultas' => 'Teknik', 'prodi' => 'TI', 'id_sms' => 'prodi-ti', 'id_fakultas' => 2],
                ['nim' => null, 'nama' => null],
                ['nim' => '2301010003', 'nama' => null],
            ],
        ], [['id' => 'prodi-ti', 'label' => 'Teknologi Informasi']]);

        $result = (new StudentSyncService($sso))->syncProdi('https://example.test/data', ['id' => 'prodi-ti', 'label' => 'Teknologi Informasi']);

        $this->assertSame(['created' => 1, 'updated' => 1, 'skipped' => 2, 'prodi' => 'Teknologi Informasi'], $result);
        $this->assertDatabaseHas('mahasiswas', ['nim' => '2301010001', 'name' => 'Baru', 'faculty' => 'Teknik']);
        $this->assertDatabaseHas('mahasiswas', ['nim' => '2301010002', 'name' => 'Baru Dua']);
        $this->assertSame(2, Mahasiswa::query()->count());
        // Sinkron mahasiswa tidak boleh menyentuh tabel anggota organisasi.
        $this->assertSame(0, Member::query()->count());
    }

    public function test_sync_all_prodi_filters_by_label(): void
    {
        $sso = $this->fakeSso([
            'a' => [['nim' => '1', 'nama' => 'Satu']],
            'b' => [['nim' => '2', 'nama' => 'Dua']],
        ], [['id' => 'a', 'label' => 'Prodi A'], ['id' => 'b', 'label' => 'Prodi B']]);

        $results = (new StudentSyncService($sso))->syncAllProdi('prodi a');

        $this->assertCount(1, $results);
        $this->assertSame('Prodi A', $results[0]['prodi']);
        $this->assertSame(1, Mahasiswa::query()->count());
    }
}
