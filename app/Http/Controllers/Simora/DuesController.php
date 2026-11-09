<?php

namespace App\Http\Controllers\Simora;

use App\Http\Controllers\Controller;
use App\Models\CashTransaction;
use App\Models\Dues;
use App\Support\OrganizationScope;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $data['proof_path'] = $request->file('proof')->store('bukti-iuran', 'local');

        DB::transaction(function () use (&$data) {
            $due = Dues::create($data);

            if ($due->status === Dues::STATUS_PAID) {
                $this->recordAsIncome($due);
            }
        });

        return redirect()->route('simora.sentra.iuran')
            ->with('status', 'Iuran berhasil dicatat.');
    }

    public function update(Request $request, Dues $due): RedirectResponse
    {
        OrganizationScope::assertCanAccessOrganization($request->user(), $due->organization_id);

        $data = $this->normalize($request->validate($this->rules(false)));

        DB::transaction(function () use ($request, $due, &$data) {
            $wasUnpaid = $due->status === Dues::STATUS_UNPAID;

            if ($request->hasFile('proof')) {
                $this->deleteProof($due->proof_path);
                $data['proof_path'] = $request->file('proof')->store('bukti-iuran', 'local');
            }

            $due->update($data);

            if ($wasUnpaid && $due->status === Dues::STATUS_PAID) {
                $this->recordAsIncome($due->fresh());
            }
        });

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

        DB::transaction(function () use ($due) {
            $locked = Dues::query()->lockForUpdate()->findOrFail($due->id);

            // Idempotency guard double-submit: sudah lunas -> tolak, jangan catat kas kedua.
            abort_if($locked->status === Dues::STATUS_PAID, 422, 'Iuran ini sudah lunas.');

            $locked->update([
                'status' => Dues::STATUS_PAID,
                'paid_at' => now(),
            ]);

            $this->recordAsIncome($locked->fresh());
        });

        return redirect()->route('simora.sentra.iuran')
            ->with('status', 'Iuran '.$due->name.' ditandai lunas dan tercatat sebagai pemasukan di Kas.');
    }

    public function download(Request $request, Dues $due)
    {
        OrganizationScope::assertCanAccessOrganization($request->user(), $due->organization_id);

        abort_if($due->proof_path === null || ! Storage::disk('local')->exists($due->proof_path), 404);

        return Storage::disk('local')->response($due->proof_path);
    }

    private function recordAsIncome(Dues $due): void
    {
        // Idempoten: jangan duplikat pemasukan untuk iuran yang sama.
        $exists = CashTransaction::query()
            ->where('organization_id', $due->organization_id)
            ->where('type', CashTransaction::TYPE_INCOME)
            ->where('title', 'Iuran '.$due->name)
            ->exists();

        if ($exists) {
            return;
        }

        CashTransaction::create([
            'organization_id' => $due->organization_id,
            'type' => CashTransaction::TYPE_INCOME,
            'title' => 'Iuran '.$due->name,
            'amount' => $due->amount,
            'date' => $due->paid_at?->format('Y-m-d') ?? now()->format('Y-m-d'),
            'category' => 'Iuran Anggota',
            'proof_path' => $due->proof_path,
        ]);
    }

    private function deleteProof(?string $path): void
    {
        if ($path !== null && str_starts_with($path, 'bukti-iuran/')) {
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
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0', 'max:999999999999'],
            'status' => ['required', Rule::in([Dues::STATUS_PAID, Dues::STATUS_UNPAID])],
            'period' => ['nullable', 'string', 'max:255'],
            'paid_at' => ['nullable', 'date'],
            'proof' => [$requireProof ? 'required' : 'nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:10240'],
        ];
    }
}
