<?php

namespace App\Services;

use App\Models\Transaction;
use App\Models\User;

class SidebarService {
    public function getPendingKycCount(): int
    {
        $query = User::query()
            ->where([
                'role' => 'user',
                'kyc_status' => 'pending'
            ]);

        return $query->count();
    }
}
