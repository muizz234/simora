<?php

namespace App\Models;

use Database\Factories\BudgetFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_id', 'work_program_id', 'program_name', 'year', 'allocated', 'realized'])]
class Budget extends Model
{
    /** @use HasFactory<BudgetFactory> */
    use HasFactory;

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function workProgram(): BelongsTo
    {
        return $this->belongsTo(WorkProgram::class);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'allocated' => 'decimal:2',
            'realized' => 'decimal:2',
        ];
    }

    /**
     * Persentase realisasi anggaran (0–100).
     *
     * @return Attribute<float, never>
     */
    protected function percentage(): Attribute
    {
        return Attribute::make(
            get: fn () => ($this->allocated !== null && (float) $this->allocated > 0)
                ? round(((float) $this->realized / (float) $this->allocated) * 100, 2)
                : 0.0,
        );
    }
}
