<?php

namespace App\Observers;

use App\Models\Budget;
use App\Modules\Shared\Events\DomainEventType;
use App\Support\DomainEventRecorder;

class BudgetObserver
{
    public function created(Budget $budget): void
    {
        DomainEventRecorder::forModel(DomainEventType::SentraBudgetCreated, $budget, $this->payload($budget));
    }

    public function updated(Budget $budget): void
    {
        DomainEventRecorder::forModel(DomainEventType::SentraBudgetUpdated, $budget, $this->payload($budget));
    }

    public function deleted(Budget $budget): void
    {
        DomainEventRecorder::forModel(DomainEventType::SentraBudgetDeleted, $budget, $this->payload($budget));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Budget $budget): array
    {
        return [
            'program_name' => $budget->program_name,
            'work_program_id' => $budget->work_program_id,
            'year' => $budget->year,
            'allocated' => $budget->allocated,
            'realized' => $budget->realized,
        ];
    }
}
