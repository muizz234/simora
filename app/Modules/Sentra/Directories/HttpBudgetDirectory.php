<?php

namespace App\Modules\Sentra\Directories;

use App\Modules\Sentra\Contracts\BudgetDirectory;
use App\Modules\Sentra\Data\BudgetSummary;
use App\Modules\Shared\Http\HttpDirectoryClient;
use Illuminate\Support\Collection;

final class HttpBudgetDirectory extends HttpDirectoryClient implements BudgetDirectory
{
    /**
     * @return Collection<int, BudgetSummary>
     */
    public function forOrganization(int $organizationId): Collection
    {
        $response = $this->client()
            ->get("/api/internal/v1/organizations/{$organizationId}/budgets")
            ->throw();

        return collect($response->json('data', []))
            ->map(fn (array $row): BudgetSummary => new BudgetSummary(
                id: (int) $row['id'],
                programName: (string) $row['program_name'],
                year: (int) $row['year'],
                allocated: (float) $row['allocated'],
                realized: (float) $row['realized'],
                workProgramId: $row['work_program_id'] !== null ? (int) $row['work_program_id'] : null,
            ));
    }

    protected function missingUrlMessage(): string
    {
        return 'URL service SENTRA belum dikonfigurasi (SENTRA_SERVICE_URL).';
    }
}
