<?php

namespace App\Modules\Sentra\Contracts;

use App\Modules\Sentra\Data\CashTransactionSummary;
use Illuminate\Support\Collection;

/**
 * Batas baca data transaksi kas SENTRA untuk modul lain.
 */
interface CashTransactionDirectory
{
    /**
     * @return Collection<int, CashTransactionSummary>
     */
    public function forOrganization(int $organizationId): Collection;
}
