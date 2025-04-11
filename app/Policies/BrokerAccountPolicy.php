<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BrokerAccountPolicy
{
    use HandlesAuthorization;

    public function access(User $user): bool
    {
        return $user->hasPermissionTo(Permission::ACCESS_ACCOUNT_LISTING);
    }

    public function accessPendingAccount(User $user): bool
    {
        return $user->hasPermissionTo(Permission::ACCESS_PENDING_ACCOUNT);
    }

    public function editPendingAccount(User $user): bool
    {
        return $user->hasPermissionTo(Permission::EDIT_PENDING_ACCOUNT);
    }
}
