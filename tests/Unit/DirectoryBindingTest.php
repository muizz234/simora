<?php

namespace Tests\Unit;

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
use Tests\TestCase;

class DirectoryBindingTest extends TestCase
{
    public function test_local_mode_binds_database_directories(): void
    {
        config(['microservices.mode' => 'local']);

        $this->assertInstanceOf(DatabaseMemberDirectory::class, $this->app->make(MemberDirectory::class));
        $this->assertInstanceOf(DatabaseWorkProgramDirectory::class, $this->app->make(WorkProgramDirectory::class));
        $this->assertInstanceOf(DatabaseLpjDirectory::class, $this->app->make(LpjDirectory::class));
        $this->assertInstanceOf(DatabaseBudgetDirectory::class, $this->app->make(BudgetDirectory::class));
        $this->assertInstanceOf(DatabaseDuesDirectory::class, $this->app->make(DuesDirectory::class));
        $this->assertInstanceOf(DatabaseCashTransactionDirectory::class, $this->app->make(CashTransactionDirectory::class));
    }

    public function test_http_mode_binds_http_directories(): void
    {
        config([
            'microservices.mode' => 'http',
            'microservices.token' => 'service-token',
            'microservices.services.orgmate.url' => 'https://orgmate.test',
            'microservices.services.eprogress.url' => 'https://eprogress.test',
            'microservices.services.sentra.url' => 'https://sentra.test',
        ]);

        $this->assertInstanceOf(HttpMemberDirectory::class, $this->app->make(MemberDirectory::class));
        $this->assertInstanceOf(HttpWorkProgramDirectory::class, $this->app->make(WorkProgramDirectory::class));
        $this->assertInstanceOf(HttpLpjDirectory::class, $this->app->make(LpjDirectory::class));
        $this->assertInstanceOf(HttpBudgetDirectory::class, $this->app->make(BudgetDirectory::class));
        $this->assertInstanceOf(HttpDuesDirectory::class, $this->app->make(DuesDirectory::class));
        $this->assertInstanceOf(HttpCashTransactionDirectory::class, $this->app->make(CashTransactionDirectory::class));
    }
}
