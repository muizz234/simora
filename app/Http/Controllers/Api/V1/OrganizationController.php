<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrganizationPublicResource;
use App\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrganizationController extends Controller
{
    /**
     * Daftar organisasi untuk landing publik (read-only).
     */
    public function index(): AnonymousResourceCollection
    {
        $organizations = Organization::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return OrganizationPublicResource::collection($organizations);
    }

    /**
     * Detail satu organisasi untuk landing publik (read-only).
     */
    public function show(Organization $organization): OrganizationPublicResource
    {
        return new OrganizationPublicResource($organization);
    }

    /**
     * Struktur & akun organisasi untuk halaman struktur publik (read-only).
     */
    public function structure(Organization $organization): JsonResponse
    {
        $positions = $organization->positions()
            ->with('member')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'description', 'member_id']);

        return response()->json([
            'organization' => new OrganizationPublicResource($organization),
            'positions' => $positions->map(fn ($position) => [
                'id' => $position->id,
                'name' => $position->name,
                'description' => $position->description,
                'holder' => $position->member ? [
                    'id' => $position->member->id,
                    'name' => $position->member->name,
                    'nim' => $position->member->nim,
                    'avatar_url' => $position->member->avatar_url,
                ] : null,
            ]),
            'members' => $organization->members()->orderBy('name')->get()->map(fn ($member) => [
                'id' => $member->id,
                'name' => $member->name,
                'nim' => $member->nim,
                'status' => $member->status,
                'avatar_url' => $member->avatar_url,
            ]),
        ]);
    }
}
