<?php

namespace App\Modules\Sentra\Data;

use Carbon\Carbon;

final readonly class CashTransactionSummary
{
    public function __construct(
        public int $id,
        public string $title,
        public string $type,
        public float $amount,
        public ?Carbon $date,
        public ?string $category,
    ) {}
}
