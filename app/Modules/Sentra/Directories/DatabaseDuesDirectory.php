<?php

namespace App\Modules\Sentra\Directories;

use App\Models\Dues;
use App\Modules\Sentra\Contracts\DuesDirectory;
use App\Modules\Sentra\Data\DuesSummary;
use Illuminate\Support\Collection;

final class DatabaseDuesDirectory implements DuesDirectory
{
    /**
     * @return Collection<int, DuesSummary>
     */
    public function forOrganization(int $organizationId): Collection
    {
        return Dues::query()
            ->where('organization_id', $organizationId)
            ->orderBy('name')
            ->orderBy('id')
            ->get(['id', 'user_id', 'name', 'amount', 'status', 'period', 'paid_at'])
            ->map(fn (Dues $dues): DuesSummary => new DuesSummary(
                id: (int) $dues->id,
                name: (string) $dues->name,
                amount: (float) $dues->amount,
                status: (string) $dues->status,
                period: $dues->period !== null ? (string) $dues->period : null,
                paidAt: $dues->paid_at,
                userId: $dues->user_id !== null ? (int) $dues->user_id : null,
            ))
            ->toBase();
    }
}
