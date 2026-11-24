<?php

namespace App\Modules\EProgress\Data;

final readonly class LpjActivitySummary
{
    public function __construct(
        public int $id,
        public string $name,
    ) {}
}
