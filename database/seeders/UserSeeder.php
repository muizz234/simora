<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class UserSeeder extends Seeder
{
    /**
     * Akun login disesuaikan dengan organisasi yang sudah ada:
     * satu akun role "organization" per organisasi (nama & foto profil
     * mengikuti logo organisasi), plus akun developer.
     * Idempoten: aman dijalankan berulang tanpa mengubah password akun lama.
     *
     * Password seed diambil dari SEED_USER_PASSWORD. Fallback "password"
     * hanya diizinkan di environment local/testing; di luar itu seeder
     * akan gagal dengan pesan yang jelas agar tidak ada kredensial
     * default yang lolos ke produksi.
     * api_token disimpan sebagai hash sha256 (seperti AccountController);
     * token plaintext hanya ditampilkan sekali ke output console, tidak
     * ditulis ke log/file.
     */
    public function run(): void
    {
        $seedPassword = $this->seedPassword();

        // Email akun organisasi per slug (mempertahankan email lama yang sudah dipakai).
        $organizationEmails = [
            'hima-ti' => 'simora.himati@gmail.com',
            'himatika' => 'simora.himatika@gmail.com',
            'himatro' => 'simora.himatro@gmail.com',
            'bemft' => 'simora.bemft@gmail.com',
            'dpmft' => 'simora.dpmft@gmail.com',
        ];

        Organization::query()->orderBy('sort_order')->get()->each(function (Organization $organization) use ($organizationEmails, $seedPassword) {
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
                $user->password = Hash::make($seedPassword);
            }

            if ($user->api_token === null) {
                $plainToken = Str::random(60);
                $user->api_token = hash('sha256', $plainToken);
                $this->command?->info("api_token (plaintext, sekali tampil) untuk {$email}: {$plainToken}");
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
            $developer->password = Hash::make($seedPassword);
        }

        if ($developer->api_token === null) {
            $plainToken = Str::random(60);
            $developer->api_token = hash('sha256', $plainToken);
            $this->command?->info("api_token (plaintext, sekali tampil) untuk simora.devlop@gmail.com: {$plainToken}");
        }

        $developer->save();
    }

    private function seedPassword(): string
    {
        $password = env('SEED_USER_PASSWORD');

        if (is_string($password) && $password !== '') {
            return $password;
        }

        if (App::environment('local', 'testing')) {
            return 'password';
        }

        throw new RuntimeException(
            'SEED_USER_PASSWORD wajib diisi saat seeding di luar local/testing. '
                .'Set SEED_USER_PASSWORD di environment sebelum menjalankan db:seed.'
        );
    }
}
