<?php

namespace App\Models;

use Database\Factories\FundRequestFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_id', 'decided_by', 'title', 'description', 'amount', 'status', 'applicant', 'decided_at'])]
class FundRequest extends Model
{
    /** @use HasFactory<FundRequestFactory> */
    use HasFactory;

    public const STATUS_PENDING = 'menunggu';

    public const STATUS_APPROVED = 'disetujui';

    public const STATUS_REJECTED = 'ditolak';

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'decided_at' => 'datetime',
        ];
    }
}
