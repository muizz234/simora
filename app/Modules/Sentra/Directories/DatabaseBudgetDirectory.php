<?php

namespace App\Modules\Sentra\Directories;

use App\Models\Budget;
use App\Modules\Sentra\Contracts\BudgetDirectory;
use App\Modules\Sentra\Data\BudgetSummary;
use Illuminate\Support\Collection;

final class DatabaseBudgetDirectory implements BudgetDirectory
{
    /**
     * @return Collection<int, BudgetSummary>
     */
    public function forOrganization(int $organizationId): Collection
    {
        return Budget::query()
            ->where('organization_id', $organizationId)
            ->orderBy('program_name')
            ->orderBy('id')
            ->get(['id', 'program_name', 'year', 'allocated', 'realized', 'work_program_id'])
            ->map(fn (Budget $budget): BudgetSummary => new BudgetSummary(
                id: (int) $budget->id,
                programName: (string) $budget->program_name,
                year: (int) $budget->year,
                allocated: (float) $budget->allocated,
                realized: (float) $budget->realized,
                workProgramId: $budget->work_program_id !== null ? (int) $budget->work_program_id : null,
            ))
            ->toBase();
    }
}
