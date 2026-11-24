<?php

namespace App\Modules\EProgress\Directories;

use App\Models\WorkProgram;
use App\Modules\EProgress\Contracts\WorkProgramDirectory;
use App\Modules\EProgress\Data\WorkProgramSummary;
use Illuminate\Support\Collection;

final class DatabaseWorkProgramDirectory implements WorkProgramDirectory
{
    /**
     * @return Collection<int, WorkProgramSummary>
     */
    public function forOrganization(int $organizationId): Collection
    {
        return WorkProgram::query()
            ->where('organization_id', $organizationId)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (WorkProgram $workProgram): WorkProgramSummary => new WorkProgramSummary(
                id: (int) $workProgram->id,
                name: (string) $workProgram->name,
            ))
            ->toBase();
    }

    public function existsForOrganization(int $workProgramId, int $organizationId): bool
    {
        return WorkProgram::query()
            ->whereKey($workProgramId)
            ->where('organization_id', $organizationId)
            ->exists();
    }
}
