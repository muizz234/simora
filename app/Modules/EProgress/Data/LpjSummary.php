<?php

namespace App\Modules\EProgress\Data;

use Carbon\CarbonInterface;

final readonly class LpjSummary
{
    public function __construct(
        public int $id,
        public string $title,
        public ?string $content,
        public string $status,
        public ?int $activityId,
        public ?int $workProgramId,
        public ?LpjActivitySummary $activity,
        public ?LpjCreatorSummary $creator,
        public ?CarbonInterface $createdAt,
    ) {}
}
