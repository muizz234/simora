<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\View\View;

class LandingStructureController extends Controller
{
    public function index(): View
    {
        $organizations = Organization::query()->orderBy('sort_order')->orderBy('name')->get();

        return view('struktur.index', compact('organizations'));
    }

    public function show(Organization $organization): View
    {
        $organization->load(['users' => fn ($query) => $query->orderBy('name')]);
        // Jabatan struktural nyata dari database beserta pemegangnya
        // (anggota organisasi dari tabel members yang ditunjuk pada
        // menu Struktur Kepengurusan).
        $positions = $organization->positions()
            ->with('member')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        $members = $organization->members()->orderBy('name')->get();

        return view('struktur.show', compact('organization', 'positions', 'members'));
    }
}
