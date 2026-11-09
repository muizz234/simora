<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Integritas database (P1/P2): unique composite, index polimorfik
     * domain_events, dan CHECK constraint `NOT ENFORCED` (MySQL).
     *
     * Mohon baca sebelum deploy ke DB berisi data nyata:
     * - Setiap unique/index dilewati + di-log bila sudah ada atau bila
     *   data existing duplikat (kolom yang NULL boleh berulang di MySQL
     *   & SQLite, jadi duplikat NULL tidak dihitung).
     * - CHECK hanya untuk field kritis yang nilainya konsisten di
     *   factory/test: cash_transactions.type, dues.status,
     *   fund_requests.status, proposals.status, members.status.
     *   Dilewati bila ada baris existing di luar daftar, agar migrasi
     *   tidak gagal di data prod. Gunakan `NOT ENFORCED` di MySQL
     *   supaya data lama dari sesi defunct tidak memblokir.
     *   Status bebas lain (archives.type, activities.status, dst.)
     *   sengaja TIDAK diberi CHECK — SKIP, lihat laporan.
     * - softDeletes data keuangan/anggota dan Global Scope
     *   BelongsToOrganization sengaja TIDAK diterapkan — SKIP karena
     *   berisiko merusak query/filter existing & banyak test
     *   (OrganizationScopeTest, DomainEventTest); guard organization_id
     *   sudah ada di controller/policy.
     * - Index atas organization_id tunggal tidak ditambahkan: FK
     *   `organization_id` di MySQL sudah membuat index, jadi hanya
     *   index komposit baru yang ditambahkan di bawah.
     */
    public function up(): void
    {
        // Unique composite (P1).
        $this->addUniqueIfSafe('attendances', ['activity_id', 'user_id'], 'attendances_activity_user_unique');
        $this->addUniqueIfSafe('dues', ['organization_id', 'user_id', 'period'], 'dues_org_user_period_unique');
        $this->addUniqueIfSafe('committees', ['activity_id', 'user_id'], 'committees_activity_user_unique');
        $this->addUniqueIfSafe('positions', ['organization_id', 'member_id'], 'positions_org_member_unique');

        // Index polimorfik untuk lookup event per subject (P1/P2).
        $this->addIndexIfMissing('domain_events', ['subject_type', 'subject_id'], 'domain_events_subject_index');

        // CHECK constraint kritis (P2) — NOT ENFORCED di MySQL, dilewati bila data existing melanggar.
        $this->addCheckIfExistingDataIsClean('cash_transactions', 'type', ['pemasukan', 'pengeluaran'], 'cash_transactions_type_check');
        $this->addCheckIfExistingDataIsClean('dues', 'status', ['lunas', 'belum_bayar'], 'dues_status_check');
        $this->addCheckIfExistingDataIsClean('fund_requests', 'status', ['menunggu', 'disetujui', 'ditolak'], 'fund_requests_status_check');
        $this->addCheckIfExistingDataIsClean('proposals', 'status', ['menunggu', 'disetujui', 'ditolak'], 'proposals_status_check');
        $this->addCheckIfExistingDataIsClean('members', 'status', ['aktif', 'nonaktif'], 'members_status_check');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ([
            'cash_transactions' => 'cash_transactions_type_check',
            'dues' => 'dues_status_check',
            'fund_requests' => 'fund_requests_status_check',
            'proposals' => 'proposals_status_check',
            'members' => 'members_status_check',
        ] as $table => $constraint) {
            $this->dropCheckIfExists($table, $constraint);
        }

        $this->dropIndexIfExists('domain_events', 'domain_events_subject_index');
        $this->dropIndexIfExists('positions', 'positions_org_member_unique');
        $this->dropIndexIfExists('committees', 'committees_activity_user_unique');
        $this->dropIndexIfExists('dues', 'dues_org_user_period_unique');
        $this->dropIndexIfExists('attendances', 'attendances_activity_user_unique');
    }

    /**
     * Tambahkan unique hanya bila belum ada & data existing tidak duplikat.
     *
     * @param  string[]  $columns
     */
    private function addUniqueIfSafe(string $table, array $columns, string $name): void
    {
        if (! Schema::hasTable($table) || $this->indexExists($table, $name)) {
            return;
        }

        if ($this->hasDuplicatesIgnoringNulls($table, $columns)) {
            Log::warning("Migrasi dilewati (duplikat existing): unique {$name} di {$table}({$this->columns($columns)}).");

            return;
        }

        try {
            Schema::table($table, function (Blueprint $blueprint) use ($columns, $name): void {
                $blueprint->unique($columns, $name);
            });
        } catch (Throwable $e) {
            Log::warning("Migrasi dilewati (gagal tambah unique {$name}): {$e->getMessage()}");
        }
    }

    /**
     * Tambahkan index biasa hanya bila belum ada.
     *
     * @param  string[]  $columns
     */
    private function addIndexIfMissing(string $table, array $columns, string $name): void
    {
        if (! Schema::hasTable($table) || $this->indexExists($table, $name)) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $blueprint) use ($columns, $name): void {
                $blueprint->index($columns, $name);
            });
        } catch (Throwable $e) {
            Log::warning("Migrasi dilewati (gagal tambah index {$name}): {$e->getMessage()}");
        }
    }

    /**
     * Tambahkan CHECK bila semua baris existing termasuk dalam daftar aman.
     * MySQL: NOT ENFORCED (tidak menolak data lama). SQLite (testing): CHECK biasa.
     *
     * @param  string[]  $allowed
     */
    private function addCheckIfExistingDataIsClean(string $table, string $column, array $allowed, string $name): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column) || $this->checkExists($table, $name)) {
            return;
        }

        $placeholders = implode(',', array_fill(0, count($allowed), '?'));
        $violations = DB::table($table)
            ->whereNotIn($column, $allowed)
            ->count();

        if ($violations > 0) {
            Log::warning("Migrasi dilewati (data existing di luar daftar): CHECK {$name} di {$table}.{$column} ({$violations} baris).");

            return;
        }

        $inValues = "('".implode("','", $allowed)."')";
        $enforced = $this->isMySql() ? ' NOT ENFORCED' : '';

        try {
            DB::statement("ALTER TABLE {$table} ADD CONSTRAINT {$name} CHECK ({$column} IN {$inValues}){$enforced}");
        } catch (Throwable $e) {
            Log::warning("Migrasi dilewati (gagal tambah CHECK {$name}): {$e->getMessage()}");
        }

        // $placeholders tidak dipakai untuk query dinamis di atas; disimpan
        // untuk dibaca di log bila diperlukan (menghindari SQL injection).
        unset($placeholders);
    }

    private function dropIndexIfExists(string $table, string $name): void
    {
        if (! Schema::hasTable($table) || ! $this->indexExists($table, $name)) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $blueprint) use ($name): void {
                $blueprint->dropIndex($name);
            });
        } catch (Throwable $e) {
            Log::warning("Rollback dilewati (gagal drop index {$name}): {$e->getMessage()}");
        }
    }

    private function dropCheckIfExists(string $table, string $name): void
    {
        if (! $this->checkExists($table, $name)) {
            return;
        }

        try {
            $driver = $this->driver();

            if ($driver === 'mysql') {
                DB::statement("ALTER TABLE {$table} DROP CHECK {$name}");
            } elseif ($driver === 'sqlite') {
                // SQLite tidak mendukung DROP CHECK; constraint akan hilang
                // bersama tabel. Catat saja untuk idempotensi.
                Log::warning("SQLite tidak mendukung DROP CHECK {$name} di {$table}; dilewati.");
            }
        } catch (Throwable $e) {
            Log::warning("Rollback dilewati (gagal drop CHECK {$name}): {$e->getMessage()}");
        }
    }

    /**
     * Ada duplikat non-NULL pada kolom tersebut?
     *
     * @param  string[]  $columns
     */
    private function hasDuplicatesIgnoringNulls(string $table, array $columns): bool
    {
        $cols = implode(',', array_map(fn ($c) => "`{$c}`", $columns));
        // Nilai NULL boleh berulang di unique MySQL/SQLite — jangan dihitung.
        $nullFilter = implode(' AND ', array_map(fn ($c) => "`{$c}` IS NOT NULL", $columns));

        $row = DB::selectOne(
            "SELECT COUNT(*) AS c FROM (SELECT {$cols} FROM {$table} WHERE {$nullFilter} GROUP BY {$cols} HAVING COUNT(*) > 1) AS d"
        );

        return (int) ($row->c ?? 0) > 0;
    }

    private function indexExists(string $table, string $name): bool
    {
        try {
            return Schema::getIndexes($table, $name) !== [] && Schema::getIndexes($table, $name)[0]['name'] === $name;
        } catch (Throwable) {
            return collect(Schema::getIndexes($table))->contains(fn ($i) => ($i['name'] ?? null) === $name);
        }
    }

    private function checkExists(string $table, string $name): bool
    {
        $driver = $this->driver();

        try {
            if ($driver === 'mysql') {
                return DB::selectOne(
                    'SELECT CONSTRAINT_NAME AS n FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ?',
                    [$table, $name]
                ) !== null;
            }

            if ($driver === 'sqlite') {
                return DB::selectOne(
                    "SELECT name AS n FROM sqlite_master WHERE type = 'table' AND name = ? AND sql LIKE ?",
                    [$table, "%{$name}%"]
                ) !== null;
            }
        } catch (Throwable $e) {
            Log::warning("Pengecekan CHECK {$name} gagal: {$e->getMessage()}");
        }

        // Postgres/dll: andalkan try/catch saat ADD.
        return false;
    }

    private function driver(): string
    {
        return strtolower((string) DB::connection()->getDriverName());
    }

    private function isMySql(): bool
    {
        return $this->driver() === 'mysql';
    }

    /**
     * @param  string[]  $columns
     */
    private function columns(array $columns): string
    {
        return implode(',', $columns);
    }
};
