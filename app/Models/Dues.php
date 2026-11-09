<?php

namespace App\Models;

use Database\Factories\DuesFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['organization_id', 'user_id', 'name', 'amount', 'status', 'paid_at', 'period', 'proof_path'])]
class Dues extends Model
{
    /** @use HasFactory<DuesFactory> */
    use HasFactory;

    public const STATUS_PAID = 'lunas';

    public const STATUS_UNPAID = 'belum_bayar';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
            'amount' => 'decimal:2',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getProofUrlAttribute(): ?string
    {
        if ($this->proof_path === null) {
            return null;
        }

        if (! Storage::disk('local')->exists($this->proof_path)) {
            return null;
        }

        return route('simora.sentra.iuran.download', $this);
    }
}
