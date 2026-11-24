<?php

namespace App\Observers;

use App\Models\Dues;
use App\Modules\Shared\Events\DomainEventType;
use App\Support\DomainEventRecorder;

class DuesObserver
{
    public function created(Dues $dues): void
    {
        DomainEventRecorder::forModel(DomainEventType::SentraDuesCreated, $dues, $this->payload($dues));

        if ($dues->status === Dues::STATUS_PAID) {
            $this->recordPaid($dues);
        }
    }

    public function updated(Dues $dues): void
    {
        DomainEventRecorder::forModel(DomainEventType::SentraDuesUpdated, $dues, $this->payload($dues));

        if ($dues->wasChanged('status') && $dues->status === Dues::STATUS_PAID) {
            $this->recordPaid($dues);
        }
    }

    public function deleted(Dues $dues): void
    {
        DomainEventRecorder::forModel(DomainEventType::SentraDuesDeleted, $dues, $this->payload($dues));
    }

    private function recordPaid(Dues $dues): void
    {
        DomainEventRecorder::forModel(DomainEventType::SentraDuesPaid, $dues, $this->payload($dues));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(Dues $dues): array
    {
        return [
            'name' => $dues->name,
            'amount' => $dues->amount,
            'status' => $dues->status,
            'period' => $dues->period,
            'paid_at' => $dues->paid_at?->toIso8601String(),
        ];
    }
}
