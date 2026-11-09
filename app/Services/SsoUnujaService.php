<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Klien Portal Informasi Pendidikan (SSO UNUJA).
 *
 * Alur resmi: POST {base}/portal/data/authorize dengan header X-Token
 * dan body {dev_id} untuk menerima URL data sementara (berubah tiap
 * authorize), lalu POST URL tersebut dengan body {filter, ...}.
 */
class SsoUnujaService
{
    /**
     * @return array{data_url: string, prodi: array<int, array{id: string, label: string}>, fakultas: array<int, array{id: string, label: string}>}
     */
    public function authorize(): array
    {
        $config = $this->config();

        $this->ensureConfigured($config);

        $response = Http::timeout(30)
            ->acceptJson()
            ->withHeaders(['X-Token' => $config['token']])
            ->post($config['base_url'].'/portal/data/authorize', ['dev_id' => $config['dev_id']]);

        $json = $response->json();

        if (! $response->successful() || ($json['success'] ?? false) !== true) {
            throw new RuntimeException('Authorize SSO gagal: '.($json['message'] ?? 'respons tidak dikenal'));
        }

        $info = $json['data']['info'] ?? [];
        $dataUrl = $info['urls']['data'] ?? null;
        $scopeProdi = $info['scope']['prodi'] ?? [];
        $scopeFakultas = $info['scope']['fakultas'] ?? [];

        if (is_string($dataUrl) === false || $dataUrl === '') {
            throw new RuntimeException('Authorize SSO gagal: URL data tidak tersedia.');
        }

        /** @var array<int, array{id: string, label: string}> $prodi */
        $prodi = $this->normalizeScope($scopeProdi);
        /** @var array<int, array{id: string, label: string}> $fakultas */
        $fakultas = $this->normalizeScope($scopeFakultas);

        return [
            'data_url' => $dataUrl,
            'prodi' => $prodi,
            'fakultas' => $fakultas,
        ];
    }

    /**
     * Ambil satu halaman mahasiswa untuk satu prodi (id_sms).
     *
     * @return array{rows: array<int, array<string, mixed>>, current_page: int, last_page: int, total: int}
     */
    public function mahasiswaPage(string $dataUrl, string $prodiId, int $page = 1): array
    {
        $config = $this->config();
        $this->ensureConfigured($config);

        $response = Http::timeout(30)
            ->acceptJson()
            ->withHeaders(['X-Token' => $config['token']])
            ->post($dataUrl, [
                'filter' => 'mahasiswa',
                'id_sms' => $prodiId,
                'page' => $page,
            ]);

        $json = $response->json();

        if (! $response->successful() || ($json['success'] ?? false) !== true) {
            throw new RuntimeException('Ambil data mahasiswa gagal: '.($json['message'] ?? 'respons tidak dikenal'));
        }

        $payload = $json['data'] ?? [];

        return [
            'rows' => is_array($payload['data'] ?? null) ? $payload['data'] : [],
            'current_page' => (int) ($payload['current_page'] ?? $page),
            'last_page' => (int) ($payload['last_page'] ?? $page),
            'total' => (int) ($payload['total'] ?? 0),
        ];
    }

    /**
     * Ambil semua mahasiswa untuk satu prodi, mengikuti paginasi.
     *
     * @return array<int, array<string, mixed>>
     */
    public function allMahasiswa(string $dataUrl, string $prodiId): array
    {
        $rows = [];
        $page = 1;

        do {
            $result = $this->mahasiswaPage($dataUrl, $prodiId, $page);
            array_push($rows, ...$result['rows']);
            $page = $result['current_page'] + 1;
            $last = $result['last_page'];
        } while ($page <= $last);

        return $rows;
    }

    /**
     * @param  array<string, mixed>  $row  Baris mentah dari SSO.
     * @return array{nim: ?string, name: ?string, faculty: ?string, study_program: ?string, prodi_id: ?string, fakultas_id: ?string, gender: ?string, enrollment_year: ?string}
     */
    public function normalizeMahasiswa(array $row): array
    {
        return [
            'nim' => $this->nullableString($row['nim'] ?? null),
            'name' => $this->nullableString($row['nama'] ?? null),
            'faculty' => $this->nullableString($row['fakultas'] ?? null),
            'study_program' => $this->nullableString($row['prodi'] ?? null),
            'prodi_id' => $this->nullableString($row['id_sms'] ?? null),
            'fakultas_id' => $this->nullableString($row['id_fakultas'] ?? null),
            'gender' => $this->nullableString($row['jenis_kelamin'] ?? null),
            'enrollment_year' => $this->nullableString($row['tahun_masuk'] ?? null),
        ];
    }

    /**
     * @return array{base_url: string, token: ?string, dev_id: ?string, app_id: ?string}
     */
    private function config(): array
    {
        return [
            'base_url' => rtrim((string) config('services.sso_unuja.base_url', 'https://sso.unuja.ac.id'), '/'),
            'token' => config('services.sso_unuja.token'),
            'dev_id' => config('services.sso_unuja.dev_id'),
            'app_id' => config('services.sso_unuja.app_id'),
        ];
    }

    /**
     * @param  array{base_url: string, token: ?string, dev_id: ?string, app_id: ?string}  $config
     */
    private function ensureConfigured(array $config): void
    {
        if (empty($config['token']) || empty($config['dev_id'])) {
            throw new RuntimeException('Kredensial SSO belum diset: isi SSO_UNUJA_TOKEN dan SSO_UNUJA_DEV_ID di .env.');
        }
    }

    /**
     * @return array<int, array{id: string, label: string}>
     */
    private function normalizeScope(mixed $scope): array
    {
        if (is_array($scope) === false) {
            return [];
        }

        $result = [];

        foreach ($scope as $item) {
            if (is_array($item) && isset($item['id'], $item['label'])) {
                $result[] = ['id' => (string) $item['id'], 'label' => (string) $item['label']];
            }
        }

        return $result;
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return trim((string) $value);
    }
}
