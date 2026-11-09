<?php

namespace App\Http\Controllers\Simora;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Models\CashTransaction;
use App\Support\OrganizationScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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

        $amount = $data['amount'];
        $proofPath = $request->file('proof')->store('bukti-realisasi', 'local');

        DB::transaction(function () use ($organization, $budget, $data, $amount, $proofPath) {
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
        });

        return redirect()->route('simora.sentra.realisasi', ['periode' => $budget->year])
            ->with('status', 'Realisasi Rp'.number_format((float) $amount, 0, ',', '.').' tercatat di Kas dan RAB.');
    }

    public function download(Request $request, CashTransaction $cashTransaction)
    {
        OrganizationScope::assertCanAccessOrganization($request->user(), $cashTransaction->organization_id);

        abort_if($cashTransaction->proof_path === null || ! Storage::disk('local')->exists($cashTransaction->proof_path), 404);

        return Storage::disk('local')->response($cashTransaction->proof_path);
    }
}
