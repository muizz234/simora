<?php

namespace App\Http\Controllers\Simora;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Data mahasiswa per prodi (SSO), khusus role developer.
 * Terpisah dari data anggota organisasi (members).
 */
class MahasiswaController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->role === 'developer', 403, 'Hanya developer yang dapat mengakses data mahasiswa per prodi.');

        $query = Mahasiswa::query();

        $prodi = trim((string) $request->query('prodi', ''));
        if ($prodi !== '') {
            $query->where('study_program', $prodi);
        }

        $search = trim((string) $request->query('q', ''));
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('nim', 'like', "%{$search}%");
            });
        }

        $perPage = min(max((int) $request->query('per_page', 10), 5), 50);
        $mahasiswas = $query->orderBy('study_program')->orderBy('name')->paginate($perPage)->withQueryString();

        $counts = Mahasiswa::query()
            ->selectRaw('study_program, COUNT(*) as total')
            ->groupBy('study_program')
            ->orderBy('study_program')
            ->pluck('total', 'study_program');

        $prodiOptions = $counts->keys()->filter()->values();

        $isDeveloper = true;
        $organizationsList = Organization::query()->orderBy('sort_order')->orderBy('name')->get(['id', 'name', 'slug']);
        $selectedOrganization = null;
        $orgName = 'Semua Organisasi';

        return view('simora.mahasiswa.index', compact(
            'mahasiswas',
            'counts',
            'prodiOptions',
            'prodi',
            'search',
            'isDeveloper',
            'organizationsList',
            'selectedOrganization',
            'orgName',
        ));
    }
}
