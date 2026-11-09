<?php

namespace App\Console\Commands;

use App\Services\StudentSyncService;
use Illuminate\Console\Command;

class SyncMahasiswaFromSso extends Command
{
    protected $signature = 'sso:sync-mahasiswa {--prodi= : Label prodi yang disinkron, mis. "Teknologi Informasi" (default: semua prodi)}';

    protected $description = 'Sinkronkan data mahasiswa (per prodi, role developer) dari SSO UNUJA ke tabel mahasiswas';

    public function handle(StudentSyncService $sync): int
    {
        $this->line('Sinkron data mahasiswa per prodi...');

        try {
            $results = $sync->syncAllProdi($this->option('prodi'));
        } catch (\Throwable $e) {
            $this->error('Gagal sinkron: '.$e->getMessage());

            return self::FAILURE;
        }

        if ($results === []) {
            $this->warn('Tidak ada prodi yang cocok.');

            return self::FAILURE;
        }

        $totalCreated = 0;
        $totalUpdated = 0;

        foreach ($results as $result) {
            $totalCreated += $result['created'];
            $totalUpdated += $result['updated'];

            $this->info("  {$result['prodi']}: {$result['created']} baru, {$result['updated']} diperbarui, {$result['skipped']} dilewati.");
        }

        $this->newLine();
        $this->info("Selesai. Total: {$totalCreated} baru, {$totalUpdated} diperbarui.");

        return self::SUCCESS;
    }
}
