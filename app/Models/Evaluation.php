<?php

namespace App\Models;

use Database\Factories\EvaluationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_id', 'activity_id', 'created_by', 'title', 'content', 'type', 'status'])]
class Evaluation extends Model
{
    /** @use HasFactory<EvaluationFactory> */
    use HasFactory;

    public const TYPE_EVALUATION = 'evaluasi';

    public const TYPE_REPORT = 'lpj';

    public const STATUS_DRAFT = 'draft';

    public const STATUS_DONE = 'selesai';

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
