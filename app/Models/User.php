<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable(['name', 'email', 'nim', 'password', 'role', 'organization_id', 'avatar_path', 'api_token'])]
#[Hidden(['password', 'remember_token', 'api_token'])]
class User extends Authenticatable
{
    public const ROLE_DEVELOPER = 'developer';

    public const ROLE_ORGANIZATION = 'organization';

    public const ROLE_USER = 'user';

    public const ROLES = [self::ROLE_DEVELOPER, self::ROLE_ORGANIZATION, self::ROLE_USER];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function memberProfile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Member::class);
    }

    public function getAvatarUrlAttribute(): ?string
    {
        // Akun role organization memakai logo organisasinya sebagai avatar:
        // satu foto yang sama untuk logo dan avatar.
        if ($this->role === self::ROLE_ORGANIZATION && $this->organization?->logo_path !== null) {
            return $this->organization->logo_url;
        }

        if ($this->avatar_path === null) {
            return null;
        }

        // Suffix versi dari waktu ubah file agar browser tidak memakai
        // cache gambar lama setelah foto diganti.
        if (Str::startsWith($this->avatar_path, 'assets/')) {
            $mtime = @filemtime(public_path($this->avatar_path));

            return asset($this->avatar_path).($mtime ? '?v='.$mtime : '');
        }

        $url = Storage::disk('public')->url($this->avatar_path);
        $mtime = @filemtime(Storage::disk('public')->path($this->avatar_path));

        return $url.($mtime ? '?v='.$mtime : '');
    }

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
