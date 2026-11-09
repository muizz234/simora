<?php

namespace App\Models;

use Database\Factories\PositionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_id', 'member_id', 'name', 'description', 'sort_order', 'group_type'])]
class Position extends Model
{
    /** @use HasFactory<PositionFactory> */
    use HasFactory;

    public const GROUPS = ['pimpinan', 'wakil_pimpinan', 'bpi', 'divisi'];

    public const GROUP_LABELS = [
        'pimpinan' => 'Pimpinan',
        'wakil_pimpinan' => 'Wakil Pimpinan',
        'bpi' => 'BPI (Badan Pengurus Inti)',
        'divisi' => 'Divisi',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
