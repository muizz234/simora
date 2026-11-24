<?php

namespace App\Modules\EProgress\Directories;

use App\Modules\EProgress\Contracts\LpjDirectory;
use App\Modules\EProgress\Data\LpjActivitySummary;
use App\Modules\EProgress\Data\LpjCreatorSummary;
use App\Modules\EProgress\Data\LpjSummary;
use App\Modules\Shared\Http\HttpDirectoryClient;
use Carbon\Carbon;
use Illuminate\Support\Collection;

final class HttpLpjDirectory extends HttpDirectoryClient implements LpjDirectory
{
    /**
     * @return Collection<int, LpjSummary>
     */
    public function forOrganization(?int $organizationId): Collection
    {
        $path = $organizationId === null
            ? '/api/internal/v1/lpjs'
            : "/api/internal/v1/organizations/{$organizationId}/lpjs";

        $response = $this->client()
            ->get($path)
            ->throw();

        return collect($response->json('data', []))
            ->map(fn (array $row): LpjSummary => new LpjSummary(
                id: (int) $row['id'],
                title: (string) $row['title'],
                content: $row['content'] ?? null,
                status: (string) $row['status'],
                activityId: $row['activity_id'] !== null ? (int) $row['activity_id'] : null,
                workProgramId: $row['work_program_id'] !== null ? (int) $row['work_program_id'] : null,
                activity: isset($row['activity'])
                    ? new LpjActivitySummary((int) $row['activity']['id'], (string) $row['activity']['name'])
                    : null,
                creator: isset($row['creator'])
                    ? new LpjCreatorSummary((string) $row['creator']['name'])
                    : null,
                createdAt: isset($row['created_at']) ? Carbon::parse($row['created_at']) : null,
            ));
    }

    protected function missingUrlMessage(): string
    {
        return 'URL service E-PROGRESS belum dikonfigurasi (EPROGRESS_SERVICE_URL).';
    }
}
