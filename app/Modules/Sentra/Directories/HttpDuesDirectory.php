<?php

namespace App\Modules\Sentra\Directories;

use App\Modules\Sentra\Contracts\DuesDirectory;
use App\Modules\Sentra\Data\DuesSummary;
use App\Modules\Shared\Http\HttpDirectoryClient;
use Carbon\Carbon;
use Illuminate\Support\Collection;

final class HttpDuesDirectory extends HttpDirectoryClient implements DuesDirectory
{
    /**
     * @return Collection<int, DuesSummary>
     */
    public function forOrganization(int $organizationId): Collection
    {
        $response = $this->client()
            ->get("/api/internal/v1/organizations/{$organizationId}/dues")
            ->throw();

        return collect($response->json('data', []))
            ->map(fn (array $row): DuesSummary => new DuesSummary(
                id: (int) $row['id'],
                name: (string) $row['name'],
                amount: (float) $row['amount'],
                status: (string) $row['status'],
                period: isset($row['period']) ? (string) $row['period'] : null,
                paidAt: isset($row['paid_at']) ? Carbon::parse($row['paid_at']) : null,
                userId: $row['user_id'] !== null ? (int) $row['user_id'] : null,
            ));
    }

    protected function missingUrlMessage(): string
    {
        return 'URL service SENTRA belum dikonfigurasi (SENTRA_SERVICE_URL).';
    }
}
