<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Organization;
use App\Models\Position;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_api_lists_organizations_without_login(): void
    {
        Organization::factory()->create(['name' => 'HIMA-TI', 'slug' => 'hima-ti']);

        $this->getJson(route('api.v1.organizations.index'))
            ->assertOk()
            ->assertJsonPath('data.0.name', 'HIMA-TI')
            ->assertJsonPath('data.0.slug', 'hima-ti')
            ->assertJsonMissingPath('data.0.password');
    }

    public function test_public_api_shows_organization_detail_with_vision_and_mission(): void
    {
        $organization = Organization::factory()->create([
            'slug' => 'hima-ti',
            'vision' => 'Visi dari API',
            'mission' => 'Misi dari API',
        ]);

        $this->getJson(route('api.v1.organizations.show', $organization))
            ->assertOk()
            ->assertJsonPath('data.vision', 'Visi dari API')
            ->assertJsonPath('data.mission', 'Misi dari API');
    }

    public function test_public_api_returns_structure_with_positions_and_members(): void
    {
        $organization = Organization::factory()->create(['slug' => 'hima-ti']);
        $member = Member::factory()->create([
            'name' => 'Ketua Contoh',
            'organization_id' => $organization->id,
        ]);
        Position::factory()->create([
            'organization_id' => $organization->id,
            'member_id' => $member->id,
            'name' => 'Ketua Umum',
        ]);

        $this->getJson(route('api.v1.organizations.structure', $organization))
            ->assertOk()
            ->assertJsonPath('organization.name', $organization->name)
            ->assertJsonPath('positions.0.name', 'Ketua Umum')
            ->assertJsonPath('positions.0.holder.name', 'Ketua Contoh')
            ->assertJsonPath('members.0.name', 'Ketua Contoh');
    }

    public function test_public_api_rejects_write_methods(): void
    {
        $this->postJson(route('api.v1.organizations.index'))->assertMethodNotAllowed();
    }
}
