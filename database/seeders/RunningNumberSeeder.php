<?php

namespace Database\Seeders;

use App\Models\RunningNumber;
use Illuminate\Database\Seeder;

class RunningNumberSeeder extends Seeder
{
    public function run(): void
    {
        RunningNumber::create([
            'type' => 'transaction',
            'prefix' => 'VA-TXN',
            'digits' => 7,
            'last_number' => 0,
        ]);

        RunningNumber::create([
            'type' => 'bonus_wallet',
            'prefix' => 'VA-BW',
            'digits' => 7,
            'last_number' => 0,
        ]);

        RunningNumber::create([
            'type' => 'cash_wallet',
            'prefix' => 'VA-CW',
            'digits' => 7,
            'last_number' => 0,
        ]);
    }
}
