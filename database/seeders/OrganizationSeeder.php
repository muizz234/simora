<?php

namespace Database\Seeders;

use App\Models\Organization;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organizations = [
            [
                'name' => 'HIMA-TI',
                'slug' => 'hima-ti',
                'full_name' => 'Himpunan Mahasiswa Teknologi Informasi',
                'banner_color' => '#171717',
                'accent_color' => '#3687ff',
                'focus' => 'Teknologi Informasi',
                'description' => 'Wadah mahasiswa Teknologi Informasi untuk berbagi ide, mengembangkan potensi, dan berkolaborasi di bidang teknologi informasi.',
                'vision' => null,
                'mission' => null,
                'logo_path' => 'assets/himati.jpg',
                'sort_order' => 1,
            ],
            [
                'name' => 'HIMATIKA',
                'slug' => 'himatika',
                'full_name' => 'Himpunan Mahasiswa Teknik Informatika',
                'banner_color' => '#DC171F',
                'accent_color' => '#ff4c54',
                'focus' => 'Teknik Informatika',
                'description' => 'Ruang bagi mahasiswa Teknik Informatika untuk bertumbuh, bertukar gagasan, dan berkarya dalam bidang informatika.',
                'vision' => null,
                'mission' => null,
                'logo_path' => 'assets/himatika.jpg',
                'sort_order' => 2,
            ],
            [
                'name' => 'HIMATRO',
                'slug' => 'himatro',
                'full_name' => 'Himpunan Mahasiswa Teknik Elektro',
                'banner_color' => '#082B65',
                'accent_color' => '#168cff',
                'focus' => 'Teknik Elektro',
                'description' => 'Wadah mahasiswa Teknik Elektro untuk membangun kolaborasi, mengembangkan potensi, dan berbagi gagasan di bidang elektro.',
                'vision' => null,
                'mission' => null,
                'logo_path' => 'assets/himatro.jpg',
                'sort_order' => 3,
            ],
        ];

        foreach ($organizations as $organization) {
            Organization::firstOrCreate(['slug' => $organization['slug']], $organization);
        }
    }
}
