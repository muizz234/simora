<?php

namespace App\Modules\Orgmate\Directories;

use App\Modules\Orgmate\Contracts\MemberDirectory;
use App\Modules\Orgmate\Data\MemberSummary;
use App\Modules\Shared\Http\HttpDirectoryClient;
use Illuminate\Support\Collection;

final class HttpMemberDirectory extends HttpDirectoryClient implements MemberDirectory
{
    /**
     * @return Collection<int, MemberSummary>
     */
    public function forOrganization(int $organizationId): Collection
    {
        $response = $this->client()
            ->get("/api/internal/v1/organizations/{$organizationId}/members")
            ->throw();

        return collect($response->json('data', []))
            ->map(fn (array $row): MemberSummary => new MemberSummary(
                id: (int) $row['id'],
                name: (string) $row['name'],
            ));
    }

    protected function missingUrlMessage(): string
    {
        return 'URL service ORGMATE belum dikonfigurasi (ORGMATE_SERVICE_URL).';
    }
}
