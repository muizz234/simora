<?php

namespace App\Modules\Orgmate\Data;

final readonly class MemberSummary
{
    public function __construct(
        public int $id,
        public string $name,
    ) {}
}
