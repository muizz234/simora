<?php

namespace App\Providers;

use App\Modules\EProgress\Contracts\LpjDirectory;
use App\Modules\EProgress\Contracts\WorkProgramDirectory;
use App\Modules\EProgress\Directories\DatabaseLpjDirectory;
use App\Modules\EProgress\Directories\DatabaseWorkProgramDirectory;
use App\Modules\EProgress\Directories\HttpLpjDirectory;
use App\Modules\EProgress\Directories\HttpWorkProgramDirectory;
use App\Modules\Orgmate\Contracts\MemberDirectory;
use App\Modules\Orgmate\Directories\DatabaseMemberDirectory;
use App\Modules\Orgmate\Directories\HttpMemberDirectory;
use App\Modules\Sentra\Contracts\BudgetDirectory;
use App\Modules\Sentra\Contracts\CashTransactionDirectory;
use App\Modules\Sentra\Contracts\DuesDirectory;
use App\Modules\Sentra\Directories\DatabaseBudgetDirectory;
use App\Modules\Sentra\Directories\DatabaseCashTransactionDirectory;
use App\Modules\Sentra\Directories\DatabaseDuesDirectory;
use App\Modules\Sentra\Directories\HttpBudgetDirectory;
use App\Modules\Sentra\Directories\HttpCashTransactionDirectory;
use App\Modules\Sentra\Directories\HttpDuesDirectory;
use Illuminate\Support\ServiceProvider;

/**
 * Mengikat batas baca antar modul. Implementasi dipilih lewat
 * config microservices.mode sehingga controller tidak tahu apakah data
 * berasal dari database lokal atau service lain.
 */
class ModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(MemberDirectory::class, function (): MemberDirectory {
            if (! $this->usesHttp()) {
                return new DatabaseMemberDirectory;
            }

            return new HttpMemberDirectory(
                $this->stringConfig('microservices.services.orgmate.url'),
                $this->stringConfig('microservices.token'),
                (int) config('microservices.timeout', 5),
            );
        });

        $this->app->bind(WorkProgramDirectory::class, function (): WorkProgramDirectory {
            if (! $this->usesHttp()) {
                return new DatabaseWorkProgramDirectory;
            }

            return new HttpWorkProgramDirectory(
                $this->stringConfig('microservices.services.eprogress.url'),
                $this->stringConfig('microservices.token'),
                (int) config('microservices.timeout', 5),
            );
        });

        $this->app->bind(LpjDirectory::class, function (): LpjDirectory {
            if (! $this->usesHttp()) {
                return new DatabaseLpjDirectory;
            }

            return new HttpLpjDirectory(
                $this->stringConfig('microservices.services.eprogress.url'),
                $this->stringConfig('microservices.token'),
                (int) config('microservices.timeout', 5),
            );
        });

        $this->app->bind(BudgetDirectory::class, function (): BudgetDirectory {
            if (! $this->usesHttp()) {
                return new DatabaseBudgetDirectory;
            }

            return new HttpBudgetDirectory(
                $this->stringConfig('microservices.services.sentra.url'),
                $this->stringConfig('microservices.token'),
                (int) config('microservices.timeout', 5),
            );
        });

        $this->app->bind(DuesDirectory::class, function (): DuesDirectory {
            if (! $this->usesHttp()) {
                return new DatabaseDuesDirectory;
            }

            return new HttpDuesDirectory(
                $this->stringConfig('microservices.services.sentra.url'),
                $this->stringConfig('microservices.token'),
                (int) config('microservices.timeout', 5),
            );
        });

        $this->app->bind(CashTransactionDirectory::class, function (): CashTransactionDirectory {
            if (! $this->usesHttp()) {
                return new DatabaseCashTransactionDirectory;
            }

            return new HttpCashTransactionDirectory(
                $this->stringConfig('microservices.services.sentra.url'),
                $this->stringConfig('microservices.token'),
                (int) config('microservices.timeout', 5),
            );
        });
    }

    private function usesHttp(): bool
    {
        return config('microservices.mode') === 'http';
    }

    private function stringConfig(string $key): ?string
    {
        $value = config($key);

        return is_string($value) && $value !== '' ? $value : null;
    }
}
