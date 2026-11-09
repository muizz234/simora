<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Akun login disesuaikan dengan organisasi yang sudah ada:
     * satu akun role "organization" per organisasi (nama & foto profil
     * mengikuti logo organisasi), plus akun developer.
     * Idempoten: aman dijalankan berulang tanpa mengubah password akun lama.
     */
    public function run(): void
    {
        // Email akun organisasi per slug (mempertahankan email lama yang sudah dipakai).
        $organizationEmails = [
            'hima-ti' => 'simora.himati@gmail.com',
            'himatika' => 'simora.himatika@gmail.com',
            'himatro' => 'simora.himatro@gmail.com',
            'bemft' => 'simora.bemft@gmail.com',
            'dpmft' => 'simora.dpmft@gmail.com',
        ];

        Organization::query()->orderBy('sort_order')->get()->each(function (Organization $organization) use ($organizationEmails) {
            $email = $organizationEmails[$organization->slug]
                ?? 'simora.'.$organization->slug.'@gmail.com';

            $user = User::firstOrNew(['email' => $email]);
            // Satu foto untuk logo organisasi dan avatar akun organisasi.
            $user->fill([
                'name' => $organization->name,
                'role' => User::ROLE_ORGANIZATION,
                'organization_id' => $organization->id,
                'avatar_path' => $organization->logo_path,
            ]);

            if (! $user->exists) {
                $user->password = Hash::make('password123');
            }

            if ($user->api_token === null) {
                $user->api_token = Str::random(60);
            }

            $user->save();
        });

        $developer = User::firstOrNew(['email' => 'simora.devlop@gmail.com']);
        $developer->fill([
            'name' => 'Devloper Apps',
            'role' => User::ROLE_DEVELOPER,
            'organization_id' => null,
            'avatar_path' => 'assets/SIMORA.png',
        ]);

        if (! $developer->exists) {
            $developer->password = Hash::make('password123');
        }

        if ($developer->api_token === null) {
            $developer->api_token = Str::random(60);
        }

        $developer->save();
    }
}
