<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Penegakan kepemilikan data antar modul SIMORA.
 *
 * Aturan kepemilikan:
 * - ORGMATE memiliki: organizations, users, positions, archives, announcements, recruitments
 * - E-PROGRESS memiliki: work_programs, activities, proposals, committees, attendances,
 *   documentations, evaluations
 * - SENTRA memiliki: cash_transactions, budgets, fund_requests, dues, inventories, asset_loans
 *
 * Modul lain hanya boleh membaca lewat relasi, tidak menulis tabel milik modul lain.
 * Role "organization" hanya boleh mengakses data organisasinya sendiri;
 * role "developer" boleh melihat semua organisasi (agregat).
 */
class OrganizationScope
{
    /**
     * Batasi query ke organisasi milik user.
     *
     * @param  Builder<*>  $query
     * @return Builder<*>
     */
    public static function forUser(Builder $query, User $user, string $foreignKey = 'organization_id'): Builder
    {
        if ($user->role === User::ROLE_DEVELOPER) {
            return $query;
        }

        return $query->where($foreignKey, $user->organization_id);
    }

    /**
     * Pastikan user boleh mengakses organisasi tertentu.
     */
    public static function assertCanAccessOrganization(User $user, ?int $organizationId): void
    {
        if ($user->role === User::ROLE_DEVELOPER) {
            return;
        }

        if ($organizationId === null || (int) $user->organization_id !== (int) $organizationId) {
            throw new AccessDeniedHttpException('Anda tidak memiliki akses ke organisasi ini.');
        }
    }

    /**
     * Tentukan ID organisasi yang berlaku untuk request:
     * developer memakai pilihan request (null = semua organisasi),
     * organization selalu terikat ke organisasinya sendiri.
     */
    public static function organizationIdFor(User $user, ?int $requestedOrganizationId = null): ?int
    {
        if ($user->role === User::ROLE_DEVELOPER) {
            return $requestedOrganizationId;
        }

        return $user->organization_id !== null ? (int) $user->organization_id : null;
    }
}
