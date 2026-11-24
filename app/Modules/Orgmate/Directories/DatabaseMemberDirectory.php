<?php

namespace App\Modules\Orgmate\Directories;

use App\Models\Member;
use App\Modules\Orgmate\Contracts\MemberDirectory;
use App\Modules\Orgmate\Data\MemberSummary;
use Illuminate\Support\Collection;

final class DatabaseMemberDirectory implements MemberDirectory
{
    /**
     * @return Collection<int, MemberSummary>
     */
    public function forOrganization(int $organizationId): Collection
    {
        return Member::query()
            ->where('organization_id', $organizationId)
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Member $member): MemberSummary => new MemberSummary(
                id: (int) $member->id,
                name: (string) $member->name,
            ))
            ->toBase();
    }
}
