<?php

namespace App\Http\Controllers\Simora;

use App\Http\Controllers\Controller;
use App\Models\FooterSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LandingManagementController extends Controller
{
    public function edit(): View
    {
        $footerSetting = FooterSetting::current();

        return view('simora.landing-management', compact('footerSetting'));
    }

    public function update(Request $request): RedirectResponse
    {
        $footerSetting = FooterSetting::current();

        $footerSetting->update($request->validate([
            'identity_text' => ['required', 'string', 'max:255'],
            'tagline' => ['required', 'string', 'max:120'],
            'instagram_url' => ['required', 'url', 'regex:/^https?:\/\//', 'max:2048'],
            'tiktok_url' => ['required', 'url', 'regex:/^https?:\/\//', 'max:2048'],
            'website_url' => ['required', 'url', 'regex:/^https?:\/\//', 'max:2048'],
        ]));

        return redirect()->route('simora.landing-management')
            ->with('status', 'Pengaturan footer berhasil disimpan.');
    }
}
