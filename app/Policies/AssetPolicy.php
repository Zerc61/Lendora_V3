<?php

namespace App\Policies;

use App\Models\Asset;
use App\Models\User;

class AssetPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role?->slug, [
            'borrower',
            'operator',
            'organization_admin',
            'admin_prodi',
            'technician',
        ], true);
    }

    public function view(User $user, Asset $asset): bool
    {
        return $this->viewAny($user) && $user->organization_id === $asset->organization_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('organization_admin');
    }

    public function update(User $user, Asset $asset): bool
    {
        return $user->hasRole('organization_admin')
            && $user->organization_id === $asset->organization_id;
    }

    public function updateCondition(User $user, Asset $asset): bool
    {
        return $this->update($user, $asset) || (
            $user->hasRole('technician')
            && $user->organization_id === $asset->organization_id
        );
    }

    public function delete(User $user, Asset $asset): bool
    {
        return $this->update($user, $asset);
    }

    public function restore(User $user, Asset $asset): bool
    {
        return $this->update($user, $asset);
    }

    public function forceDelete(User $user, Asset $asset): bool
    {
        return $this->update($user, $asset);
    }

    public function retire(User $user, Asset $asset): bool
    {
        return $this->update($user, $asset);
    }
}
