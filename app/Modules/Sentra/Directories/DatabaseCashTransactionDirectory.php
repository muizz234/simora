<?php

namespace App\Modules\Sentra\Directories;

use App\Models\CashTransaction;
use App\Modules\Sentra\Contracts\CashTransactionDirectory;
use App\Modules\Sentra\Data\CashTransactionSummary;
use Illuminate\Support\Collection;

final class DatabaseCashTransactionDirectory implements CashTransactionDirectory
{
    /**
     * @return Collection<int, CashTransactionSummary>
     */
    public function forOrganization(int $organizationId): Collection
    {
        return CashTransaction::query()
            ->where('organization_id', $organizationId)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get(['id', 'title', 'type', 'amount', 'date', 'category'])
            ->map(fn (CashTransaction $transaction): CashTransactionSummary => new CashTransactionSummary(
                id: (int) $transaction->id,
                title: (string) $transaction->title,
                type: (string) $transaction->type,
                amount: (float) $transaction->amount,
                date: $transaction->date,
                category: $transaction->category !== null ? (string) $transaction->category : null,
            ))
            ->toBase();
    }
}
