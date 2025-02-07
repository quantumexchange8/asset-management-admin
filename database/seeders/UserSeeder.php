<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Super Admin',
            'username' => 'superadmin',
            'email' => 'admin@admin.com',
            'email_verified_at' => now(),
            'password' =>  Hash::make('testtest'),
            'remember_token' => Str::random(10),
            'dial_code' => '60',
            'phone' => '123456789',
            'phone_number' => '60123456789',
            'country_id' => 132,
            'nationality' => 'Malaysian',
            'referral_code' => 'VTAx666666',
            'id_number' => 'SID000001',
            'role' => 'super_admin',
        ]);
    }
}
