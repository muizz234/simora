<?php

namespace App\Models;

use Database\Factories\ArchiveFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable(['organization_id', 'uploaded_by', 'title', 'type', 'number', 'date', 'file_path'])]
class Archive extends Model
{
    /** @use HasFactory<ArchiveFactory> */
    use HasFactory;

    public const TYPE_SK = 'sk';

    public const TYPE_INCOMING = 'surat_masuk';

    public const TYPE_OUTGOING = 'surat_keluar';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getFileUrlAttribute(): ?string
    {
        if ($this->file_path === null) {
            return null;
        }

        if (Str::startsWith($this->file_path, 'assets/')) {
            return asset($this->file_path);
        }

        $url = Storage::disk('public')->url($this->file_path);
        $mtime = @filemtime(Storage::disk('public')->path($this->file_path));

        return $url.($mtime ? '?v='.$mtime : '');
    }
}
