<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;

class SidebarService
{
    public function getPendingKycCount(): int
    {
        $query = User::query()
            ->where([
                'role' => 'user',
                'kyc_status' => 'pending'
            ]);

        return $query->count();
    }

    public function getPendingDepositCount(): int
    {
        $query = Transaction::query()
            ->where([
                'status' => 'processing',
                'transaction_type' => 'deposit'
            ]);

        return $query->count();
    }
}
