<?php

namespace App\Modules\EProgress\Directories;

use App\Models\Evaluation;
use App\Modules\EProgress\Contracts\LpjDirectory;
use App\Modules\EProgress\Data\LpjActivitySummary;
use App\Modules\EProgress\Data\LpjCreatorSummary;
use App\Modules\EProgress\Data\LpjSummary;
use Illuminate\Support\Collection;

final class DatabaseLpjDirectory implements LpjDirectory
{
    /**
     * @return Collection<int, LpjSummary>
     */
    public function forOrganization(?int $organizationId): Collection
    {
        return Evaluation::query()
            ->with(['activity:id,name,work_program_id', 'creator:id,name'])
            ->where('type', Evaluation::TYPE_REPORT)
            ->when($organizationId !== null, fn ($query) => $query->where('organization_id', $organizationId))
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Evaluation $evaluation): LpjSummary => new LpjSummary(
                id: (int) $evaluation->id,
                title: (string) $evaluation->title,
                content: $evaluation->content,
                status: (string) $evaluation->status,
                activityId: $evaluation->activity_id !== null ? (int) $evaluation->activity_id : null,
                workProgramId: $evaluation->activity?->work_program_id !== null
                    ? (int) $evaluation->activity->work_program_id
                    : null,
                activity: $evaluation->activity !== null
                    ? new LpjActivitySummary((int) $evaluation->activity->id, (string) $evaluation->activity->name)
                    : null,
                creator: $evaluation->creator !== null
                    ? new LpjCreatorSummary((string) $evaluation->creator->name)
                    : null,
                createdAt: $evaluation->created_at,
            ))
            ->toBase()
            ->values();
    }
}
