<?php

namespace Tests\Unit;

use App\Modules\Sentra\Directories\HttpBudgetDirectory;
use App\Modules\Sentra\Directories\HttpCashTransactionDirectory;
use App\Modules\Sentra\Directories\HttpDuesDirectory;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class HttpSentraDirectoryTest extends TestCase
{
    public function test_http_budget_directory_maps_internal_api_response(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'https://sentra.test/api/internal/v1/organizations/7/budgets' => Http::response([
                'data' => [
                    [
                        'id' => 12,
                        'program_name' => 'RAB Seminar',
                        'year' => 2026,
                        'allocated' => '1500000.00',
                        'realized' => '450000.00',
                        'work_program_id' => 11,
                    ],
                ],
            ]),
        ]);

        $budgets = (new HttpBudgetDirectory('https://sentra.test/', 'service-token'))
            ->forOrganization(7);

        $this->assertCount(1, $budgets);
        $this->assertSame('RAB Seminar', $budgets->first()->programName);
        $this->assertSame(1500000.0, $budgets->first()->allocated);
        $this->assertSame(11, $budgets->first()->workProgramId);

        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://sentra.test/api/internal/v1/organizations/7/budgets'
            && $request->hasHeader('Authorization', 'Bearer service-token'));
    }

    public function test_http_dues_directory_maps_internal_api_response(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'https://sentra.test/api/internal/v1/organizations/7/dues' => Http::response([
                'data' => [
                    [
                        'id' => 8,
                        'name' => 'Siti Aminah',
                        'amount' => '50000.00',
                        'status' => 'lunas',
                        'period' => 'November 2026',
                        'paid_at' => '2026-11-01T10:00:00+00:00',
                        'user_id' => 3,
                    ],
                ],
            ]),
        ]);

        $dues = (new HttpDuesDirectory('https://sentra.test', 'service-token'))
            ->forOrganization(7);

        $this->assertCount(1, $dues);
        $this->assertSame('Siti Aminah', $dues->first()->name);
        $this->assertSame(50000.0, $dues->first()->amount);
        $this->assertSame('lunas', $dues->first()->status);
        $this->assertSame('2026-11-01', $dues->first()->paidAt?->toDateString());
        $this->assertSame(3, $dues->first()->userId);

        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://sentra.test/api/internal/v1/organizations/7/dues'
            && $request->hasHeader('Authorization', 'Bearer service-token'));
    }

    public function test_http_cash_transaction_directory_maps_internal_api_response(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'https://sentra.test/api/internal/v1/organizations/7/cash-transactions' => Http::response([
                'data' => [
                    [
                        'id' => 21,
                        'title' => 'Kuitansi Seminar',
                        'type' => 'pengeluaran',
                        'amount' => '250000.00',
                        'date' => '2026-11-01',
                        'category' => 'Kegiatan',
                    ],
                ],
            ]),
        ]);

        $transactions = (new HttpCashTransactionDirectory('https://sentra.test', 'service-token'))
            ->forOrganization(7);

        $this->assertCount(1, $transactions);
        $this->assertSame('Kuitansi Seminar', $transactions->first()->title);
        $this->assertSame('pengeluaran', $transactions->first()->type);
        $this->assertSame(250000.0, $transactions->first()->amount);
        $this->assertSame('2026-11-01', $transactions->first()->date?->toDateString());
        $this->assertSame('Kegiatan', $transactions->first()->category);

        Http::assertSent(fn (Request $request): bool => $request->url() === 'https://sentra.test/api/internal/v1/organizations/7/cash-transactions'
            && $request->hasHeader('Authorization', 'Bearer service-token'));
    }

    public function test_http_directory_retries_server_errors_before_succeeding(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'https://sentra.test/api/internal/v1/organizations/7/budgets' => Http::sequence()
                ->push(['data' => []], 500)
                ->push(['data' => []], 503)
                ->push(['data' => [
                    [
                        'id' => 12,
                        'program_name' => 'RAB Seminar',
                        'year' => 2026,
                        'allocated' => '100.00',
                        'realized' => '0.00',
                        'work_program_id' => null,
                    ],
                ]], 200),
        ]);

        $budgets = (new HttpBudgetDirectory('https://sentra.test', 'service-token'))
            ->forOrganization(7);

        $this->assertCount(1, $budgets);
        $this->assertSame('RAB Seminar', $budgets->first()->programName);
        Http::assertSentCount(3);
    }

    public function test_http_directory_does_not_retry_client_errors(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            'https://sentra.test/api/internal/v1/organizations/7/budgets' => Http::response([
                'message' => 'Unauthorized',
            ], 401),
        ]);

        try {
            (new HttpBudgetDirectory('https://sentra.test', 'service-token'))
                ->forOrganization(7);
            $this->fail('RequestException harus dilempar untuk respons 401.');
        } catch (RequestException $exception) {
            $this->assertSame(401, $exception->response->status());
        }

        Http::assertSentCount(1);
    }

    public function test_http_directory_reports_missing_service_url(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('SENTRA_SERVICE_URL');

        (new HttpBudgetDirectory(null, 'service-token'))->forOrganization(7);
    }
}
