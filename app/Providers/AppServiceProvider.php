<?php

namespace App\Providers;

use App\Models\Activity;
use App\Models\Budget;
use App\Models\CashTransaction;
use App\Models\Dues;
use App\Models\Evaluation;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Position;
use App\Models\User;
use App\Models\WorkProgram;
use App\Observers\ActivityObserver;
use App\Observers\BudgetObserver;
use App\Observers\CashTransactionObserver;
use App\Observers\DuesObserver;
use App\Observers\EvaluationObserver;
use App\Observers\MemberObserver;
use App\Observers\OrganizationObserver;
use App\Observers\PositionObserver;
use App\Observers\UserObserver;
use App\Observers\WorkProgramObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Internal API dipakai antar-service, kuotanya dihitung per token.
        RateLimiter::for('internal-api', function (Request $request): Limit {
            $identity = $request->header('X-Service-Token')
                ?? $request->bearerToken()
                ?? $request->ip()
                ?? 'anonymous';

            return Limit::perMinute((int) config('microservices.rate_limit.per_minute', 120))
                ->by(hash('sha256', (string) $identity));
        });

        // Event sinkronisasi antar modul (outbox domain_events).
        Organization::observe(OrganizationObserver::class);
        User::observe(UserObserver::class);

        // Event domain per modul untuk konsumen lintas service.
        Member::observe(MemberObserver::class);
        Position::observe(PositionObserver::class);
        WorkProgram::observe(WorkProgramObserver::class);
        Activity::observe(ActivityObserver::class);
        Evaluation::observe(EvaluationObserver::class);
        Budget::observe(BudgetObserver::class);
        Dues::observe(DuesObserver::class);
        CashTransaction::observe(CashTransactionObserver::class);
    }
}
