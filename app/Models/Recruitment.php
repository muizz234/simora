<?php

namespace App\Models;

use Database\Factories\RecruitmentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_id', 'created_by', 'title', 'division', 'description', 'deadline', 'status'])]
class Recruitment extends Model
{
    /** @use HasFactory<RecruitmentFactory> */
    use HasFactory;

    public const STATUS_OPEN = 'dibuka';

    public const STATUS_CLOSED = 'ditutup';

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
