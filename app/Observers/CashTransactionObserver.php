<?php

namespace App\Observers;

use App\Models\CashTransaction;
use App\Modules\Shared\Events\DomainEventType;
use App\Support\DomainEventRecorder;

class CashTransactionObserver
{
    public function created(CashTransaction $cashTransaction): void
    {
        DomainEventRecorder::forModel(DomainEventType::SentraCashTransactionCreated, $cashTransaction, $this->payload($cashTransaction));
    }

    public function updated(CashTransaction $cashTransaction): void
    {
        DomainEventRecorder::forModel(DomainEventType::SentraCashTransactionUpdated, $cashTransaction, $this->payload($cashTransaction));
    }

    public function deleted(CashTransaction $cashTransaction): void
    {
        DomainEventRecorder::forModel(DomainEventType::SentraCashTransactionDeleted, $cashTransaction, $this->payload($cashTransaction));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(CashTransaction $cashTransaction): array
    {
        return [
            'type' => $cashTransaction->type,
            'title' => $cashTransaction->title,
            'amount' => $cashTransaction->amount,
            'category' => $cashTransaction->category,
            'date' => $cashTransaction->date?->toDateString(),
        ];
    }
}
