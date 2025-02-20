<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BrokerPolicy
{
    use HandlesAuthorization;

    public function access(User $user): bool
    {
        return $user->hasPermissionTo(Permission::ACCESS_BROKER);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::ADD_BROKER);
    }

    public function update(User $user): bool
    {
        return $user->hasPermissionTo(Permission::EDIT_BROKER);
    }

    public function delete(User $user): bool
    {
        return $user->hasPermissionTo(Permission::DELETE_BROKER);
    }
}
