<?php

namespace App\Http\Controllers\Api\Internal\V1;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\CashTransaction;
use App\Models\Dues;
use App\Models\Evaluation;
use App\Models\Member;
use App\Models\WorkProgram;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

/**
 * Internal read API untuk konsumsi service lain.
 * Hanya data minimum yang dibutuhkan lintas modul (tanpa PII).
 */
class DirectoryController extends Controller
{
    public function members(int $organization): JsonResponse
    {
        $members = Member::query()
            ->where('organization_id', $organization)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json(['data' => $members]);
    }

    public function workPrograms(int $organization): JsonResponse
    {
        $workPrograms = WorkProgram::query()
            ->where('organization_id', $organization)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json(['data' => $workPrograms]);
    }

    public function lpjs(int $organization): JsonResponse
    {
        return response()->json(['data' => $this->lpjPayload($organization)]);
    }

    public function allLpjs(): JsonResponse
    {
        return response()->json(['data' => $this->lpjPayload(null)]);
    }

    public function budgets(int $organization): JsonResponse
    {
        $budgets = Budget::query()
            ->where('organization_id', $organization)
            ->orderBy('program_name')
            ->orderBy('id')
            ->get(['id', 'program_name', 'year', 'allocated', 'realized', 'work_program_id']);

        return response()->json(['data' => $budgets]);
    }

    public function dues(int $organization): JsonResponse
    {
        $dues = Dues::query()
            ->where('organization_id', $organization)
            ->orderBy('name')
            ->orderBy('id')
            ->get(['id', 'user_id', 'name', 'amount', 'status', 'period', 'paid_at']);

        return response()->json(['data' => $dues]);
    }

    public function cashTransactions(int $organization): JsonResponse
    {
        $transactions = CashTransaction::query()
            ->where('organization_id', $organization)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get(['id', 'title', 'type', 'amount', 'date', 'category']);

        return response()->json(['data' => $transactions]);
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function lpjPayload(?int $organization): Collection
    {
        return Evaluation::query()
            ->with(['activity:id,name,work_program_id', 'creator:id,name'])
            ->when($organization !== null, fn ($query) => $query->where('organization_id', $organization))
            ->where('type', Evaluation::TYPE_REPORT)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Evaluation $evaluation): array => [
                'id' => $evaluation->id,
                'title' => $evaluation->title,
                'content' => $evaluation->content,
                'status' => $evaluation->status,
                'activity_id' => $evaluation->activity_id,
                'work_program_id' => $evaluation->activity?->work_program_id,
                'activity' => $evaluation->activity !== null
                    ? ['id' => $evaluation->activity->id, 'name' => $evaluation->activity->name]
                    : null,
                'creator' => $evaluation->creator !== null
                    ? ['name' => $evaluation->creator->name]
                    : null,
                'created_at' => $evaluation->created_at?->toIso8601String(),
            ]);
    }
}
