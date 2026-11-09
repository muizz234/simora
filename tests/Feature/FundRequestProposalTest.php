<?php

namespace Tests\Feature;

use App\Models\FooterSetting;
use App\Models\FundRequest;
use App\Models\Organization;
use App\Models\Proposal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FundRequestProposalTest extends TestCase
{
    use RefreshDatabase;

    public function test_fund_request_factory_and_relations(): void
    {
        $organization = Organization::factory()->create();
        $fundRequest = FundRequest::factory()->create(['organization_id' => $organization->id]);

        $this->assertTrue($fundRequest->organization->is($organization));
        $this->assertTrue($organization->fundRequests->contains($fundRequest));
        $this->assertContains($fundRequest->status, [FundRequest::STATUS_PENDING, FundRequest::STATUS_APPROVED, FundRequest::STATUS_REJECTED]);
        $this->assertNull($fundRequest->decider);
    }

    public function test_proposal_factory_and_relations(): void
    {
        $organization = Organization::factory()->create();
        $proposal = Proposal::factory()->create(['organization_id' => $organization->id]);

        $this->assertTrue($proposal->organization->is($organization));
        $this->assertTrue($organization->proposals->contains($proposal));
        $this->assertContains($proposal->status, [Proposal::STATUS_PENDING, Proposal::STATUS_APPROVED, Proposal::STATUS_REJECTED]);
        $this->assertNull($proposal->activity);
        $this->assertNull($proposal->submitter);
    }

    public function test_footer_setting_current_creates_once_and_returns_existing(): void
    {
        $this->assertSame(0, FooterSetting::query()->count());

        $first = FooterSetting::current();
        $second = FooterSetting::current();

        $this->assertTrue($first->is($second));
        $this->assertSame(1, FooterSetting::query()->count());
        $this->assertNotEmpty($first->identity_text);
        $this->assertNotEmpty($first->website_url);
    }
}
