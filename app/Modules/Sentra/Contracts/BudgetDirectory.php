<?php

namespace App\Modules\Sentra\Contracts;

use App\Modules\Sentra\Data\BudgetSummary;
use Illuminate\Support\Collection;

/**
 * Batas baca data anggaran RAB SENTRA untuk modul lain.
 *
 * Implementasi lokal memakai database; implementasi HTTP memanggil
 * internal API SENTRA saat modul sudah dipisah menjadi service.
 */
interface BudgetDirectory
{
    /**
     * @return Collection<int, BudgetSummary>
     */
    public function forOrganization(int $organizationId): Collection;
}
