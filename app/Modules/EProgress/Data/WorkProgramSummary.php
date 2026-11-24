<?php

namespace App\Modules\EProgress\Data;

final readonly class WorkProgramSummary
{
    public function __construct(
        public int $id,
        public string $name,
    ) {}
}
