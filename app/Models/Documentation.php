<?php

namespace App\Models;

use Database\Factories\DocumentationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['organization_id', 'activity_id', 'uploaded_by', 'title', 'type', 'file_path'])]
class Documentation extends Model
{
    /** @use HasFactory<DocumentationFactory> */
    use HasFactory;

    public const TYPES = ['foto', 'video', 'dokumen', 'lainnya'];

    public function getFileUrlAttribute(): ?string
    {
        if ($this->file_path === null) {
            return null;
        }

        $url = Storage::disk('public')->url($this->file_path);
        $mtime = @filemtime(Storage::disk('public')->path($this->file_path));

        return $url.($mtime ? '?v='.$mtime : '');
    }

    public function getIsImageAttribute(): bool
    {
        return in_array(strtolower(pathinfo((string) $this->file_path, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'webp'], true);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
