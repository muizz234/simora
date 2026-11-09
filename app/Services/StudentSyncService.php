<?php

namespace App\Services;

use App\Models\Mahasiswa;
use Illuminate\Support\Facades\DB;

/**
 * Sinkronkan mahasiswa SSO UNUJA ke tabel mahasiswas (data per prodi,
 * hak akses developer). BUKAN ke members (data anggota organisasi).
 */
class StudentSyncService
{
    public function __construct(private readonly SsoUnujaService $sso) {}

    /**
     * Sinkron satu prodi berdasarkan id_sms SSO.
     *
     * @return array{created: int, updated: int, skipped: int, prodi: ?string}
     */
    public function syncProdi(string $dataUrl, array $prodi): array
    {
        $rows = $this->sso->allMahasiswa($dataUrl, $prodi['id']);

        $skipped = 0;
        $upsertRows = [];
        $seen = [];

        foreach ($rows as $row) {
            $normalized = $this->sso->normalizeMahasiswa($row);

            if ($normalized['nim'] === null || $normalized['name'] === null) {
                $skipped++;

                continue;
            }

            // Deduplikat dalam payload yang sama: baris terakhir menang.
            $seen[(string) $normalized['nim']] = [
                'nim' => $normalized['nim'],
                'name' => $normalized['name'],
                'faculty' => $normalized['faculty'],
                'study_program' => $normalized['study_program'] ?? $prodi['label'],
                'prodi_id' => $normalized['prodi_id'] ?? $prodi['id'],
                'fakultas_id' => $normalized['fakultas_id'],
                'gender' => $normalized['gender'],
                'enrollment_year' => $normalized['enrollment_year'],
                'synced_at' => now()->toDateTimeString(),
                'created_at' => now()->toDateTimeString(),
                'updated_at' => now()->toDateTimeString(),
            ];
        }

        $upsertRows = array_values($seen);

        $created = 0;
        $updated = 0;

        if ($upsertRows !== []) {
            $existingNims = Mahasiswa::query()
                ->whereIn('nim', array_column($upsertRows, 'nim'))
                ->pluck('nim')
                ->all();

            $existingLookup = array_flip($existingNims);

            DB::transaction(function () use ($upsertRows) {
                foreach (array_chunk($upsertRows, 500) as $chunk) {
                    Mahasiswa::upsert($chunk, uniqueBy: ['nim'], update: ['name', 'faculty', 'study_program', 'prodi_id', 'fakultas_id', 'gender', 'enrollment_year', 'synced_at']);
                }
            });

            foreach ($upsertRows as $record) {
                if (isset($existingLookup[(string) $record['nim']])) {
                    $updated++;
                } else {
                    $created++;
                }
            }
        }

        return [
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'prodi' => $prodi['label'] ?? null,
        ];
    }

    /**
     * Sinkron semua prodi dalam scope authorize.
     * Bila $filterLabel diisi, hanya prodi dengan label/id yang cocok.
     *
     * @return array<int, array{prodi: ?string, created: int, updated: int, skipped: int}>
     */
    public function syncAllProdi(?string $filterLabel = null): array
    {
        $auth = $this->sso->authorize();

        $prodiList = collect($auth['prodi'])
            ->when($filterLabel !== null, fn ($q) => $q->filter(fn ($p) => strtolower((string) $p['label']) === strtolower($filterLabel) || (string) $p['id'] === $filterLabel))
            ->values();

        $results = [];

        foreach ($prodiList as $prodi) {
            $results[] = $this->syncProdi($auth['data_url'], $prodi);
        }

        return $results;
    }
}
