<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['identity_text', 'tagline', 'instagram_url', 'tiktok_url', 'website_url'])]
class FooterSetting extends Model
{
    public static function current(): self
    {
        return self::query()->firstOrCreate([], [
            'identity_text' => 'Universitas Nurul Jadid · Bersama Membangun Fakultas Teknik yang Lebih Baik',
            'tagline' => 'Kolaborasi • Inovasi • Prestasi',
            'instagram_url' => 'https://instagram.com',
            'tiktok_url' => 'https://tiktok.com',
            'website_url' => 'https://unuja.ac.id',
        ]);
    }
}
