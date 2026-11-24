<?php

namespace App\Modules\Sentra\Contracts;

use App\Modules\Sentra\Data\DuesSummary;
use Illuminate\Support\Collection;

/**
 * Batas baca data iuran SENTRA untuk modul lain.
 */
interface DuesDirectory
{
    /**
     * @return Collection<int, DuesSummary>
     */
    public function forOrganization(int $organizationId): Collection;
}
