<?php

namespace App\Modules\Sentra\Data;

use Carbon\Carbon;

final readonly class DuesSummary
{
    public function __construct(
        public int $id,
        public string $name,
        public float $amount,
        public string $status,
        public ?string $period,
        public ?Carbon $paidAt,
        public ?int $userId,
    ) {}
}
