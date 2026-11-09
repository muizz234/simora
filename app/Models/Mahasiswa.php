<?php

namespace App\Models;

use Database\Factories\MahasiswaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['nim', 'name', 'faculty', 'study_program', 'prodi_id', 'fakultas_id', 'gender', 'enrollment_year', 'synced_at'])]
class Mahasiswa extends Model
{
    /** @use HasFactory<MahasiswaFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'synced_at' => 'datetime',
        ];
    }
}
