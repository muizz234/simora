<?php

namespace Tests\Unit;

use App\Services\SsoUnujaService;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class SsoUnujaServiceTest extends TestCase
{
    private function configureSso(): void
    {
        config()->set('services.sso_unuja.base_url', 'https://sso.example.test');
        config()->set('services.sso_unuja.token', 'token-uji');
        config()->set('services.sso_unuja.dev_id', 'dev-uji');
    }

    public function test_authorize_returns_data_url_and_normalized_scope(): void
    {
        $this->configureSso();
        Http::fake([
            'sso.example.test/portal/data/authorize' => Http::response([
                'success' => true,
                'data' => ['info' => [
                    'urls' => ['data' => 'https://example.test/data-sementara'],
                    'scope' => [
                        'prodi' => [['id' => 5, 'label' => 'Teknologi Informasi'], ['bogus' => true]],
                        'fakultas' => 'bukan-array',
                    ],
                ]],
            ]),
        ]);

        $result = (new SsoUnujaService)->authorize();

        $this->assertSame('https://example.test/data-sementara', $result['data_url']);
        // id dinormalisasi ke string, item tanpa id/label dibuang.
        $this->assertSame([['id' => '5', 'label' => 'Teknologi Informasi']], $result['prodi']);
        $this->assertSame([], $result['fakultas']);
        Http::assertSent(fn ($request) => $request->header('X-Token')[0] === 'token-uji'
            && $request['dev_id'] === 'dev-uji');
    }

    public function test_authorize_throws_when_credentials_missing(): void
    {
        $this->configureSso();
        config()->set('services.sso_unuja.token', null);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Kredensial SSO belum diset');

        (new SsoUnujaService)->authorize();
    }

    public function test_authorize_throws_when_url_data_missing(): void
    {
        $this->configureSso();
        Http::fake([
            'sso.example.test/portal/data/authorize' => Http::response(['success' => true, 'data' => ['info' => []]]),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('URL data tidak tersedia');

        (new SsoUnujaService)->authorize();
    }

    public function test_authorize_throws_on_failed_response(): void
    {
        $this->configureSso();
        Http::fake([
            'sso.example.test/portal/data/authorize' => Http::response(['success' => false, 'message' => 'token salah'], 403),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('token salah');

        (new SsoUnujaService)->authorize();
    }

    public function test_all_mahasiswa_follows_pagination(): void
    {
        $this->configureSso();
        Http::fake([
            'example.test/data-sementara' => Http::sequence()
                ->push(['success' => true, 'data' => ['data' => [['nim' => '1']], 'current_page' => 1, 'last_page' => 2, 'total' => 2]])
                ->push(['success' => true, 'data' => ['data' => [['nim' => '2']], 'current_page' => 2, 'last_page' => 2, 'total' => 2]]),
        ]);

        $rows = (new SsoUnujaService)->allMahasiswa('https://example.test/data-sementara', 'prodi-ti');

        $this->assertSame([['nim' => '1'], ['nim' => '2']], $rows);
        Http::assertSentCount(2);
    }

    public function test_normalize_mahasiswa_maps_fields_and_trims(): void
    {
        $normalized = (new SsoUnujaService)->normalizeMahasiswa([
            'nim' => ' 2301010001 ',
            'nama' => ' Budi Santoso ',
            'fakultas' => 'Teknik',
            'prodi' => 'Teknologi Informasi',
            'id_sms' => 5,
            'id_fakultas' => '',
            'jenis_kelamin' => 'L',
            'tahun_masuk' => '2023',
        ]);

        $this->assertSame('2301010001', $normalized['nim']);
        $this->assertSame('Budi Santoso', $normalized['name']);
        $this->assertSame('Teknik', $normalized['faculty']);
        $this->assertSame('5', $normalized['prodi_id']);
        $this->assertNull($normalized['fakultas_id']);
        $this->assertNull((new SsoUnujaService)->normalizeMahasiswa([])['nim']);
    }
}
