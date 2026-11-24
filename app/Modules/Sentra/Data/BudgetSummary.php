<?php

namespace App\Modules\Sentra\Data;

final readonly class BudgetSummary
{
    public function __construct(
        public int $id,
        public string $programName,
        public int $year,
        public float $allocated,
        public float $realized,
        public ?int $workProgramId,
    ) {}
}
