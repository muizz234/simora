<?php

namespace App\Observers;

use App\Models\WorkProgram;
use App\Modules\Shared\Events\DomainEventType;
use App\Support\DomainEventRecorder;

class WorkProgramObserver
{
    public function created(WorkProgram $workProgram): void
    {
        DomainEventRecorder::forModel(DomainEventType::EProgressWorkProgramCreated, $workProgram, $this->payload($workProgram));
    }

    public function updated(WorkProgram $workProgram): void
    {
        DomainEventRecorder::forModel(DomainEventType::EProgressWorkProgramUpdated, $workProgram, $this->payload($workProgram));
    }

    public function deleted(WorkProgram $workProgram): void
    {
        DomainEventRecorder::forModel(DomainEventType::EProgressWorkProgramDeleted, $workProgram, $this->payload($workProgram));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(WorkProgram $workProgram): array
    {
        return [
            'name' => $workProgram->name,
            'status' => $workProgram->status,
            'start_date' => $workProgram->start_date?->toDateString(),
            'end_date' => $workProgram->end_date?->toDateString(),
        ];
    }
}
