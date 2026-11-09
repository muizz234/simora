<?php

namespace App\Http\Controllers\Simora;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrganizationVisionMissionController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $organization = $request->user()->organization;

        abort_if($organization === null, 403, 'Akun ini belum terhubung ke organisasi.');

        $data = $request->validate([
            'vision' => ['nullable', 'string', 'max:5000'],
            'mission' => ['nullable', 'string', 'max:5000'],
        ]);

        $organization->update($data);

        return redirect()->route('panel')
            ->with('status', 'Visi dan misi berhasil disimpan dan akan tampil di landing publik.');
    }
}
