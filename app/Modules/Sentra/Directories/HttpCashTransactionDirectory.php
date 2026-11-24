<?php

namespace App\Modules\Sentra\Directories;

use App\Modules\Sentra\Contracts\CashTransactionDirectory;
use App\Modules\Sentra\Data\CashTransactionSummary;
use App\Modules\Shared\Http\HttpDirectoryClient;
use Carbon\Carbon;
use Illuminate\Support\Collection;

final class HttpCashTransactionDirectory extends HttpDirectoryClient implements CashTransactionDirectory
{
    /**
     * @return Collection<int, CashTransactionSummary>
     */
    public function forOrganization(int $organizationId): Collection
    {
        $response = $this->client()
            ->get("/api/internal/v1/organizations/{$organizationId}/cash-transactions")
            ->throw();

        return collect($response->json('data', []))
            ->map(fn (array $row): CashTransactionSummary => new CashTransactionSummary(
                id: (int) $row['id'],
                title: (string) $row['title'],
                type: (string) $row['type'],
                amount: (float) $row['amount'],
                date: isset($row['date']) ? Carbon::parse($row['date']) : null,
                category: isset($row['category']) ? (string) $row['category'] : null,
            ));
    }

    protected function missingUrlMessage(): string
    {
        return 'URL service SENTRA belum dikonfigurasi (SENTRA_SERVICE_URL).';
    }
}
