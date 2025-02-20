<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BrokerConnectionPolicy
{
    use HandlesAuthorization;

    public function access(User $user): bool
    {
        return $user->hasPermissionTo(Permission::ACCESS_CONNECTIONS);
    }

    public function import(User $user): bool
    {
        return $user->hasPermissionTo(Permission::IMPORT_CONNECTIONS);
    }
}
