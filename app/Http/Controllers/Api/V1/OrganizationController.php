<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrganizationPublicResource;
use App\Models\Organization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class OrganizationController extends Controller
{
    /**
     * Daftar organisasi untuk landing publik (read-only).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $organizations = Organization::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(min(max((int) $request->query('per_page', 15), 1), 100));

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
     *
     * Paginasi: positions/members dipaginasi (default 15 per halaman,
     * maks 100, via ?positions_per_page= / ?members_per_page= atau
     * ?per_page= untuk keduanya). Daftar tetap berupa array agar
     * konsumen lama (positions.0.*, members.0.*) tidak pecah; metadata
     * paginasi ditambahkan terpisah.
     *
     * PII publik: nim & status TIDAK dikeluarkan di endpoint publik ini.
     * Hanya nama/fakultas/avatar yang memang untuk display publik.
     */
    public function structure(Request $request, Organization $organization): JsonResponse
    {
        $defaultPerPage = min(max((int) $request->query('per_page', 15), 1), 100);
        $positionsPerPage = min(max((int) $request->query('positions_per_page', $defaultPerPage), 1), 100);
        $membersPerPage = min(max((int) $request->query('members_per_page', $defaultPerPage), 1), 100);

        $positions = $organization->positions()
            ->with('member')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate($positionsPerPage, ['*'], 'positions_page')
            ->withQueryString();

        $members = $organization->members()
            ->orderBy('name')
            ->paginate($membersPerPage, ['*'], 'members_page')
            ->withQueryString();

        return response()->json([
            'organization' => new OrganizationPublicResource($organization),
            'positions' => $positions->getCollection()->map(fn ($position) => [
                'id' => $position->id,
                'name' => $position->name,
                'description' => $position->description,
                'holder' => $position->member ? [
                    'id' => $position->member->id,
                    'name' => $position->member->name,
                    'faculty' => $position->member->faculty,
                    'avatar_url' => $position->member->avatar_url,
                ] : null,
            ]),
            'positions_pagination' => [
                'current_page' => $positions->currentPage(),
                'per_page' => $positions->perPage(),
                'total' => $positions->total(),
                'last_page' => $positions->lastPage(),
            ],
            'members' => $members->getCollection()->map(fn ($member) => [
                'id' => $member->id,
                'name' => $member->name,
                'faculty' => $member->faculty,
                'avatar_url' => $member->avatar_url,
            ]),
            'members_pagination' => [
                'current_page' => $members->currentPage(),
                'per_page' => $members->perPage(),
                'total' => $members->total(),
                'last_page' => $members->lastPage(),
            ],
        ]);
    }
}
