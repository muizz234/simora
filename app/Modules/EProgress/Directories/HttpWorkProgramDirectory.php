<?php

namespace App\Modules\EProgress\Directories;

use App\Modules\EProgress\Contracts\WorkProgramDirectory;
use App\Modules\EProgress\Data\WorkProgramSummary;
use App\Modules\Shared\Http\HttpDirectoryClient;
use Illuminate\Support\Collection;

final class HttpWorkProgramDirectory extends HttpDirectoryClient implements WorkProgramDirectory
{
    /**
     * @return Collection<int, WorkProgramSummary>
     */
    public function forOrganization(int $organizationId): Collection
    {
        $response = $this->client()
            ->get("/api/internal/v1/organizations/{$organizationId}/work-programs")
            ->throw();

        return collect($response->json('data', []))
            ->map(fn (array $row): WorkProgramSummary => new WorkProgramSummary(
                id: (int) $row['id'],
                name: (string) $row['name'],
            ));
    }

    public function existsForOrganization(int $workProgramId, int $organizationId): bool
    {
        return $this->forOrganization($organizationId)
            ->contains(fn (WorkProgramSummary $workProgram): bool => $workProgram->id === $workProgramId);
    }

    protected function missingUrlMessage(): string
    {
        return 'URL service E-PROGRESS belum dikonfigurasi (EPROGRESS_SERVICE_URL).';
    }
}
