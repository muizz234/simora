<?php

namespace App\Modules\EProgress\Contracts;

use App\Modules\EProgress\Data\LpjSummary;
use Illuminate\Support\Collection;

/**
 * Batas baca dokumen LPJ E-PROGRESS untuk SENTRA (Realisasi).
 */
interface LpjDirectory
{
    /**
     * @param  int|null  $organizationId  null berarti semua organisasi (developer).
     * @return Collection<int, LpjSummary>
     */
    public function forOrganization(?int $organizationId): Collection;
}
