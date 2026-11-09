<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\CashTransaction;
use App\Models\Dues;
use App\Models\Organization;
use App\Models\User;
use App\Models\WorkProgram;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PanelController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user()->load('organization');

        $greeting = $this->greeting();
        $todayLabel = now()->locale('id')->translatedFormat('l, d F Y');

        if ($user->role === 'organization') {
            $organizationId = $user->organization_id;

            $memberCount = $organizationId
                ? $user->organization->users()->count()
                : 0;

            $stats = [
                'proker' => $organizationId ? WorkProgram::where('organization_id', $organizationId)->count() : 0,
                'kegiatan' => $organizationId ? Activity::where('organization_id', $organizationId)->count() : 0,
                'saldoKas' => $organizationId ? $this->cashBalance($organizationId) : 0,
                'iuranLunas' => $organizationId
                    ? Dues::where('organization_id', $organizationId)->where('status', Dues::STATUS_PAID)->count()
                    : 0,
                'iuranTotal' => $organizationId ? Dues::where('organization_id', $organizationId)->count() : 0,
            ];

            $recentActivities = $organizationId
                ? Activity::with('workProgram')
                    ->where('organization_id', $organizationId)
                    ->latest('start_at')
                    ->limit(5)
                    ->get()
                : collect();

            return view('simora.organization-panel', compact(
                'user',
                'memberCount',
                'greeting',
                'todayLabel',
                'stats',
                'recentActivities',
            ));
        }

        $stats = [
            'organisasi' => Organization::count(),
            'akun' => User::count(),
            'akunOrganisasi' => User::where('role', User::ROLE_ORGANIZATION)->count(),
            'proker' => WorkProgram::count(),
        ];

        $recentOrganizations = Organization::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit(6)
            ->get();

        return view('simora.panel', compact(
            'user',
            'greeting',
            'todayLabel',
            'stats',
            'recentOrganizations',
        ));
    }

    private function cashBalance(int $organizationId): float
    {
        $income = (float) CashTransaction::where('organization_id', $organizationId)
            ->where('type', CashTransaction::TYPE_INCOME)
            ->sum('amount');

        $expense = (float) CashTransaction::where('organization_id', $organizationId)
            ->where('type', CashTransaction::TYPE_EXPENSE)
            ->sum('amount');

        return $income - $expense;
    }

    private function greeting(): string
    {
        $hour = (int) now()->format('G');

        return match (true) {
            $hour >= 5 && $hour < 12 => 'Selamat pagi',
            $hour >= 12 && $hour < 15 => 'Selamat siang',
            $hour >= 15 && $hour < 18 => 'Selamat sore',
            default => 'Selamat malam',
        };
    }
}
