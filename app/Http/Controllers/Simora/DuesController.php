<?php

namespace App\Http\Controllers\Simora;

use App\Http\Controllers\Controller;
use App\Models\CashTransaction;
use App\Models\Dues;
use App\Support\OrganizationScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class DuesController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $organization = $request->user()->organization;

        abort_if($organization === null, 403, 'Akun ini belum terhubung ke organisasi.');

        $data = $this->normalize($request->validate($this->rules(true)));
        $data['organization_id'] = $organization->id;
        $data['proof_path'] = $request->file('proof')->storePublicly('bukti-iuran', 'public');

        $due = Dues::create($data);

        if ($due->status === Dues::STATUS_PAID) {
            $this->recordAsIncome($due);
        }

        return redirect()->route('simora.sentra.iuran')
            ->with('status', 'Iuran berhasil dicatat.');
    }

    public function update(Request $request, Dues $due): RedirectResponse
    {
        OrganizationScope::assertCanAccessOrganization($request->user(), $due->organization_id);

        $wasUnpaid = $due->status === Dues::STATUS_UNPAID;
        $data = $this->normalize($request->validate($this->rules(false)));

        if ($request->hasFile('proof')) {
            $this->deleteProof($due->proof_path);
            $data['proof_path'] = $request->file('proof')->storePublicly('bukti-iuran', 'public');
        }

        $due->update($data);

        if ($wasUnpaid && $due->status === Dues::STATUS_PAID) {
            $this->recordAsIncome($due);
        }

        return redirect()->route('simora.sentra.iuran')
            ->with('status', 'Iuran berhasil diperbarui.');
    }

    public function destroy(Request $request, Dues $due): RedirectResponse
    {
        OrganizationScope::assertCanAccessOrganization($request->user(), $due->organization_id);

        $this->deleteProof($due->proof_path);
        $due->delete();

        return redirect()->route('simora.sentra.iuran')
            ->with('status', 'Data iuran berhasil dihapus.');
    }

    public function markPaid(Request $request, Dues $due): RedirectResponse
    {
        OrganizationScope::assertCanAccessOrganization($request->user(), $due->organization_id);

        abort_if($due->status === Dues::STATUS_PAID, 422, 'Iuran ini sudah lunas.');

        $due->update([
            'status' => Dues::STATUS_PAID,
            'paid_at' => now(),
        ]);

        $this->recordAsIncome($due->fresh());

        return redirect()->route('simora.sentra.iuran')
            ->with('status', 'Iuran '.$due->name.' ditandai lunas dan tercatat sebagai pemasukan di Kas.');
    }

    private function recordAsIncome(Dues $due): void
    {
        CashTransaction::create([
            'organization_id' => $due->organization_id,
            'type' => CashTransaction::TYPE_INCOME,
            'title' => 'Iuran '.$due->name,
            'amount' => (float) $due->amount,
            'date' => ($due->paid_at ?? now())->format('Y-m-d'),
            'category' => 'Iuran Anggota',
            'proof_path' => $due->proof_path,
        ]);
    }

    private function deleteProof(?string $path): void
    {
        if ($path !== null && str_starts_with($path, 'bukti-iuran/')) {
            Storage::disk('public')->delete($path);
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
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0', 'max:999999999999'],
            'status' => ['required', Rule::in([Dues::STATUS_PAID, Dues::STATUS_UNPAID])],
            'period' => ['nullable', 'string', 'max:255'],
            'paid_at' => ['nullable', 'date'],
            'proof' => [$requireProof ? 'required' : 'nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
        ];
    }
}
