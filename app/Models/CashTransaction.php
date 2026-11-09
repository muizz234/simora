<?php

namespace App\Models;

use Database\Factories\CashTransactionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['organization_id', 'created_by', 'type', 'title', 'amount', 'date', 'category', 'proof_path'])]
class CashTransaction extends Model
{
    /** @use HasFactory<CashTransactionFactory> */
    use HasFactory;

    public const TYPE_INCOME = 'pemasukan';

    public const TYPE_EXPENSE = 'pengeluaran';

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getProofUrlAttribute(): ?string
    {
        if ($this->proof_path === null) {
            return null;
        }

        if (! Storage::disk('local')->exists($this->proof_path)) {
            return null;
        }

        return route('simora.sentra.kas.download', $this);
    }
}
