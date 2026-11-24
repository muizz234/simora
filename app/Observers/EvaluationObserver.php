<?php

namespace App\Observers;

use App\Models\Evaluation;
use App\Modules\Shared\Events\DomainEventType;
use App\Support\DomainEventRecorder;

class EvaluationObserver
{
    public function created(Evaluation $evaluation): void
    {
        DomainEventRecorder::forModel(DomainEventType::EProgressEvaluationCreated, $evaluation, $this->payload($evaluation));
        $this->recordLpjPublished($evaluation);
    }

    public function updated(Evaluation $evaluation): void
    {
        DomainEventRecorder::forModel(DomainEventType::EProgressEvaluationUpdated, $evaluation, $this->payload($evaluation));

        if ($evaluation->wasChanged(['type', 'status', 'title', 'content'])) {
            $this->recordLpjPublished($evaluation);
        }
    }

    public function deleted(Evaluation $evaluation): void
    {
        DomainEventRecorder::forModel(DomainEventType::EProgressEvaluationDeleted, $evaluation, $this->payload($evaluation));
    }

    private function recordLpjPublished(Evaluation $evaluation): void
    {
        if ($evaluation->type !== Evaluation::TYPE_REPORT || $evaluation->status !== Evaluation::STATUS_DONE) {
            return;
        }

        DomainEventRecorder::forModel(DomainEventType::EProgressLpjPublished, $evaluation, $this->payload($evaluation));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Evaluation $evaluation): array
    {
        return [
            'title' => $evaluation->title,
            'type' => $evaluation->type,
            'status' => $evaluation->status,
            'activity_id' => $evaluation->activity_id,
        ];
    }
}
