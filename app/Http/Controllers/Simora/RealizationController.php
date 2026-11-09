<?php

namespace App\Http\Controllers\Simora;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\CashTransaction;
use App\Support\OrganizationScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RealizationController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $organization = $request->user()->organization;

        abort_if($organization === null, 403, 'Akun ini belum terhubung ke organisasi.');

        $data = $request->validate([
            'budget_id' => ['required', 'exists:budgets,id'],
            'amount' => ['required', 'numeric', 'min:1', 'max:999999999999'],
            'date' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:255'],
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
        ]);

        $budget = Budget::findOrFail($data['budget_id']);
        OrganizationScope::assertCanAccessOrganization($request->user(), $budget->organization_id);

        $amount = (float) $data['amount'];
        $proofPath = $request->file('proof')->storePublicly('bukti-realisasi', 'public');

        CashTransaction::create([
            'organization_id' => $organization->id,
            'type' => CashTransaction::TYPE_EXPENSE,
            'title' => $data['description'] ?? 'Realisasi '.$budget->program_name,
            'amount' => $amount,
            'date' => $data['date'],
            'category' => 'Realisasi Anggaran',
            'proof_path' => $proofPath,
        ]);

        $budget->increment('realized', $amount);

        return redirect()->route('simora.sentra.realisasi', ['periode' => $budget->year])
            ->with('status', 'Realisasi Rp'.number_format($amount, 0, ',', '.').' tercatat di Kas dan RAB.');
    }
}
