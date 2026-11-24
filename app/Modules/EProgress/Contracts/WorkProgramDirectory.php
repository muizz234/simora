<?php

namespace App\Modules\EProgress\Contracts;

use App\Modules\EProgress\Data\WorkProgramSummary;
use Illuminate\Support\Collection;

/**
 * Batas baca data program kerja E-PROGRESS untuk modul lain (mis. SENTRA).
 */
interface WorkProgramDirectory
{
    /**
     * @return Collection<int, WorkProgramSummary>
     */
    public function forOrganization(int $organizationId): Collection;

    public function existsForOrganization(int $workProgramId, int $organizationId): bool;
}
