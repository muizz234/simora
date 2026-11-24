<?php

namespace App\Modules\Orgmate\Contracts;

use App\Modules\Orgmate\Data\MemberSummary;
use Illuminate\Support\Collection;

/**
 * Batas baca data anggota ORGMATE untuk modul lain.
 *
 * Implementasi lokal memakai database; implementasi HTTP memanggil
 * internal API ORGMATE saat modul sudah dipisah menjadi service.
 */
interface MemberDirectory
{
    /**
     * @return Collection<int, MemberSummary>
     */
    public function forOrganization(int $organizationId): Collection;
}
