<?php

namespace Tests\Unit;

use App\Modules\Shared\Events\EventPublishCircuitBreaker;
use Tests\TestCase;

class EventPublishCircuitBreakerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'microservices.circuit_breaker.failure_threshold' => 2,
            'microservices.circuit_breaker.cooldown_seconds' => 60,
        ]);
    }

    public function test_breaker_stays_closed_below_failure_threshold(): void
    {
        $breaker = new EventPublishCircuitBreaker;

        $breaker->recordFailure();

        $this->assertSame('closed', $breaker->status());
        $this->assertFalse($breaker->isOpen());
    }

    public function test_breaker_opens_after_failure_threshold(): void
    {
        $breaker = new EventPublishCircuitBreaker;

        $breaker->recordFailure();
        $breaker->recordFailure();

        $this->assertSame('open', $breaker->status());
        $this->assertTrue($breaker->isOpen());
    }

    public function test_breaker_allows_one_probe_after_cooldown_and_resets_on_success(): void
    {
        $breaker = new EventPublishCircuitBreaker;
        $breaker->recordFailure();
        $breaker->recordFailure();

        $this->travel(61)->seconds();

        $this->assertFalse($breaker->isOpen());
        $this->assertSame('half_open', $breaker->status());
        $this->assertTrue($breaker->isOpen());

        $breaker->recordSuccess();

        $this->assertSame('closed', $breaker->status());
        $this->assertFalse($breaker->isOpen());
    }

    public function test_failed_probe_reopens_breaker(): void
    {
        $breaker = new EventPublishCircuitBreaker;
        $breaker->recordFailure();
        $breaker->recordFailure();

        $this->travel(61)->seconds();
        $this->assertFalse($breaker->isOpen());

        $breaker->recordFailure();

        $this->assertSame('open', $breaker->status());
        $this->assertTrue($breaker->isOpen());
    }
}
