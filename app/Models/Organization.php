<?php

namespace App\Models;

use Database\Factories\OrganizationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable(['name', 'slug', 'full_name', 'banner_color', 'accent_color', 'focus', 'period', 'registration_url', 'instagram_url', 'tiktok_url', 'youtube_url', 'whatsapp_url', 'facebook_url', 'x_url', 'email', 'telegram_url', 'description', 'vision', 'mission', 'logo_path', 'sort_order'])]
class Organization extends Model
{
    /** @use HasFactory<OrganizationFactory> */
    use HasFactory;

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    // --- ORGMATE ---
    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }

    public function archives(): HasMany
    {
        return $this->hasMany(Archive::class);
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    public function recruitments(): HasMany
    {
        return $this->hasMany(Recruitment::class);
    }

    // --- E-PROGRESS ---
    public function workPrograms(): HasMany
    {
        return $this->hasMany(WorkProgram::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(Proposal::class);
    }

    public function committees(): HasMany
    {
        return $this->hasMany(Committee::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function documentations(): HasMany
    {
        return $this->hasMany(Documentation::class);
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }

    // --- SENTRA ---
    public function cashTransactions(): HasMany
    {
        return $this->hasMany(CashTransaction::class);
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    public function fundRequests(): HasMany
    {
        return $this->hasMany(FundRequest::class);
    }

    public function dues(): HasMany
    {
        return $this->hasMany(Dues::class);
    }

    public function getLogoUrlAttribute(): ?string
    {
        if ($this->logo_path === null) {
            return null;
        }

        // Suffix versi dari waktu ubah file agar browser tidak memakai
        // cache logo lama setelah logo diganti.
        if (Str::startsWith($this->logo_path, 'assets/')) {
            $mtime = @filemtime(public_path($this->logo_path));

            return asset($this->logo_path).($mtime ? '?v='.$mtime : '');
        }

        $url = Storage::disk('public')->url($this->logo_path);
        $mtime = @filemtime(Storage::disk('public')->path($this->logo_path));

        return $url.($mtime ? '?v='.$mtime : '');
    }
}
