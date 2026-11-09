<?php

namespace App\Http\Controllers\Simora;

use App\Http\Controllers\Controller;
use App\Models\CashTransaction;
use App\Support\OrganizationScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CashTransactionController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $organization = $request->user()->organization;

        abort_if($organization === null, 403, 'Akun ini belum terhubung ke organisasi.');

        $data = $this->normalize($request->validate($this->rules(true)));
        $data['organization_id'] = $organization->id;
        $data['created_by'] = $request->user()->id;
        $data['proof_path'] = $request->file('proof')->store('bukti-kas', 'local');

        CashTransaction::create($data);

        return redirect()->route('simora.sentra.kas')
            ->with('status', 'Transaksi kas berhasil ditambahkan.');
    }

    public function update(Request $request, CashTransaction $cashTransaction): RedirectResponse
    {
        OrganizationScope::assertCanAccessOrganization($request->user(), $cashTransaction->organization_id);

        $data = $this->normalize($request->validate($this->rules(false)));

        if ($request->hasFile('proof')) {
            $this->deleteProof($cashTransaction->proof_path);
            $data['proof_path'] = $request->file('proof')->store('bukti-kas', 'local');
        }

        $cashTransaction->update($data);

        return redirect()->route('simora.sentra.kas')
            ->with('status', 'Transaksi kas berhasil diperbarui.');
    }

    public function destroy(Request $request, CashTransaction $cashTransaction): RedirectResponse
    {
        OrganizationScope::assertCanAccessOrganization($request->user(), $cashTransaction->organization_id);

        $this->deleteProof($cashTransaction->proof_path);
        $cashTransaction->delete();

        return redirect()->route('simora.sentra.kas')
            ->with('status', 'Transaksi kas berhasil dihapus.');
    }

    public function download(Request $request, CashTransaction $cashTransaction)
    {
        OrganizationScope::assertCanAccessOrganization($request->user(), $cashTransaction->organization_id);

        abort_if($cashTransaction->proof_path === null || ! Storage::disk('local')->exists($cashTransaction->proof_path), 404);

        return Storage::disk('local')->response($cashTransaction->proof_path);
    }

    private function deleteProof(?string $path): void
    {
        if ($path !== null && str_starts_with($path, 'bukti-kas/')) {
            Storage::disk('local')->delete($path);
        }
    }

    private function normalize(array $data): array
    {
        foreach ($data as $key => $value) {
            if ($value === '') {
                $data[$key] = null;
            }
        }

        return $data;
    }

    private function rules(bool $requireProof = true): array
    {
        return [
            'type' => ['required', Rule::in([CashTransaction::TYPE_INCOME, CashTransaction::TYPE_EXPENSE])],
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0', 'max:999999999999'],
            'date' => ['required', 'date'],
            'category' => ['nullable', 'string', 'max:255'],
            'proof' => [$requireProof ? 'required' : 'nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
        ];
    }
}
