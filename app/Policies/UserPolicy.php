<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function access(User $user): bool
    {
        return $user->hasPermissionTo(Permission::ACCESS_MEMBER_LISTING);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::ADD_MEMBER);
    }

    public function update(User $user): bool
    {
        return $user->hasPermissionTo(Permission::EDIT_MEMBER);
    }

    public function delete(User $user): bool
    {
        return $user->hasPermissionTo(Permission::DELETE_MEMBER);
    }

    public function accessKyc(User $user): bool
    {
        return $user->hasPermissionTo(Permission::ACCESS_PENDING_KYC);
    }

    public function manageKyc(User $user): bool
    {
        return $user->hasPermissionTo(Permission::EDIT_PENDING_KYC);
    }

    public function accessAdminListing(User $user): bool
    {
        return $user->hasPermissionTo(Permission::ACCESS_ADMIN_LISTING);
    }
}
