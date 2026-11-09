<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use Illuminate\View\View;

class LandingKegiatanController extends Controller
{
    public function index(): View
    {
        // Halaman publik Kegiatan: hanya kegiatan yang sudah punya
        // dokumentasi dari E-PROGRESS, urut terbaru dulu.
        $activities = Activity::query()
            ->with(['organization', 'workProgram', 'documentations'])
            ->whereHas('documentations')
            ->orderByDesc('start_at')
            ->get();

        return view('kegiatan.index', compact('activities'));
    }

    public function show(Activity $activity): View
    {
        $activity->load(['organization', 'workProgram', 'documentations' => fn ($query) => $query->orderByDesc('created_at')]);

        abort_if($activity->documentations->isEmpty(), 404);

        $related = Activity::query()
            ->with(['organization', 'workProgram', 'documentations'])
            ->whereHas('documentations')
            ->where('id', '!=', $activity->id)
            ->where('organization_id', $activity->organization_id)
            ->orderByDesc('start_at')
            ->take(3)
            ->get();

        return view('kegiatan.show', compact('activity', 'related'));
    }
}
