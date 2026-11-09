<?php

namespace App\Models;

use Database\Factories\MemberFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable(['organization_id', 'user_id', 'name', 'nim', 'email', 'phone', 'faculty', 'division', 'status', 'avatar_path'])]
class Member extends Model
{
    /** @use HasFactory<MemberFactory> */
    use HasFactory;

    public const STATUS_ACTIVE = 'aktif';

    public const STATUS_INACTIVE = 'nonaktif';

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if ($this->avatar_path === null) {
            return null;
        }

        if (Str::startsWith($this->avatar_path, 'assets/')) {
            $mtime = @filemtime(public_path($this->avatar_path));

            return asset($this->avatar_path).($mtime ? '?v='.$mtime : '');
        }

        $url = Storage::disk('public')->url($this->avatar_path);
        $mtime = @filemtime(Storage::disk('public')->path($this->avatar_path));

        return $url.($mtime ? '?v='.$mtime : '');
    }
}
