<?php

namespace Tests\Unit;

use App\Modules\EProgress\Directories\HttpLpjDirectory;
use App\Modules\EProgress\Directories\HttpWorkProgramDirectory;
use App\Modules\Orgmate\Directories\HttpMemberDirectory;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class HttpDirectoryTest extends TestCase
{
    public function test_http_member_directory_maps_internal_api_response(): void
    {
        Http::fake([
            'https://orgmate.test/api/internal/v1/organizations/7/members' => Http::response([
                'data' => [
                    ['id' => 3, 'name' => 'Rina'],
                ],
            ]),
        ]);

        $members = (new HttpMemberDirectory('https://orgmate.test', 'service-token'))
            ->forOrganization(7);

        $this->assertCount(1, $members);
        $this->assertSame(3, $members->first()->id);
        $this->assertSame('Rina', $members->first()->name);

        Http::assertSent(fn ($request): bool => $request->url() === 'https://orgmate.test/api/internal/v1/organizations/7/members'
            && $request->hasHeader('Authorization', 'Bearer service-token'));
    }

    public function test_http_work_program_directory_can_verify_ownership(): void
    {
        Http::fake([
            'https://eprogress.test/api/internal/v1/organizations/7/work-programs' => Http::response([
                'data' => [
                    ['id' => 11, 'name' => 'Proker A'],
                ],
            ]),
        ]);

        $directory = new HttpWorkProgramDirectory('https://eprogress.test', 'service-token');

        $this->assertTrue($directory->existsForOrganization(11, 7));
        $this->assertFalse($directory->existsForOrganization(99, 7));
    }

    public function test_http_lpj_directory_maps_activity_and_creator(): void
    {
        Http::fake([
            'https://eprogress.test/api/internal/v1/organizations/7/lpjs' => Http::response([
                'data' => [
                    [
                        'id' => 5,
                        'title' => 'LPJ Seminar',
                        'content' => 'Isi laporan',
                        'status' => 'selesai',
                        'activity_id' => 2,
                        'work_program_id' => 11,
                        'activity' => ['id' => 2, 'name' => 'Seminar'],
                        'creator' => ['name' => 'Budi'],
                        'created_at' => '2026-11-01T10:00:00+00:00',
                    ],
                ],
            ]),
        ]);

        $lpjs = (new HttpLpjDirectory('https://eprogress.test', 'service-token'))
            ->forOrganization(7);

        $this->assertCount(1, $lpjs);
        $this->assertSame('LPJ Seminar', $lpjs->first()->title);
        $this->assertSame(11, $lpjs->first()->workProgramId);
        $this->assertSame('Seminar', $lpjs->first()->activity?->name);
        $this->assertSame('Budi', $lpjs->first()->creator?->name);
        $this->assertSame('2026-11-01', $lpjs->first()->createdAt?->toDateString());
    }
}
