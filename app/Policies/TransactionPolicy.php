<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TransactionPolicy
{
    use HandlesAuthorization;

    public function accessPendingDeposit(User $user): bool
    {
        return $user->hasPermissionTo(Permission::ACCESS_PENDING_DEPOSIT);
    }

    public function editPendingDeposit(User $user): bool
    {
        return $user->hasPermissionTo(Permission::EDIT_PENDING_DEPOSIT);
    }

    public function accessPendingWithdrawal(User $user): bool
    {
        return $user->hasPermissionTo(Permission::ACCESS_PENDING_WITHDRAWAL);
    }

    public function editPendingWithdrawal(User $user): bool
    {
        return $user->hasPermissionTo(Permission::EDIT_PENDING_WITHDRAWAL);
    }

    public function accessDepositHistory(User $user): bool
    {
        return $user->hasPermissionTo(Permission::ACCESS_DEPOSIT_HISTORY);
    }

    public function accessWithdrawalHistory(User $user): bool
    {
        return $user->hasPermissionTo(Permission::ACCESS_WITHDRAWAL_HISTORY);
    }
}
