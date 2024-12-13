<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('ranks')->insert([
            [
                'id' => 1,
                'rank_name' => 'member',
                'rank_position' => 1,
                'lot_rebate_currency' => 'USD',
                'lot_rebate_amount' => 0,
                'min_direct_referral' => null,
                'min_direct_referral_rank_id' => null,
                'min_amount_per_person' => 1000,
                'min_group_sales' => 0,
                'edited_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 2,
                'rank_name' => 'IB1',
                'rank_position' => 2,
                'lot_rebate_currency' => 'USD',
                'lot_rebate_amount' => 0,
                'min_direct_referral' => 3,
                'min_direct_referral_rank_id' => 1,
                'min_amount_per_person' => 1000,
                'min_group_sales' => 10000,
                'edited_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 3,
                'rank_name' => 'IB2',
                'rank_position' => 3,
                'lot_rebate_currency' => 'USD',
                'lot_rebate_amount' => 0,
                'min_direct_referral' => 2,
                'min_direct_referral_rank_id' => 2,
                'min_amount_per_person' => 3000,
                'min_group_sales' => 50000,
                'edited_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 4,
                'rank_name' => 'IB3',
                'rank_position' => 4,
                'lot_rebate_currency' => 'USD',
                'lot_rebate_amount' => 0,
                'min_direct_referral' => 2,
                'min_direct_referral_rank_id' => 3,
                'min_amount_per_person' => 5000,
                'min_group_sales' => 250000,
                'edited_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 5,
                'rank_name' => 'IB4',
                'rank_position' => 5,
                'lot_rebate_currency' => 'USD',
                'lot_rebate_amount' => 4,
                'min_direct_referral' => 2,
                'min_direct_referral_rank_id' => 4,
                'min_amount_per_person' => 10000,
                'min_group_sales' => 1000000,
                'edited_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 6,
                'rank_name' => 'IB5',
                'rank_position' => 6,
                'lot_rebate_currency' => 'USD',
                'lot_rebate_amount' => 7,
                'min_direct_referral' => 2,
                'min_direct_referral_rank_id' => 5,
                'min_amount_per_person' => 15000,
                'min_group_sales' => 3000000,
                'edited_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
            [
                'id' => 7,
                'rank_name' => 'IB6',
                'rank_position' => 7,
                'lot_rebate_currency' => 'USD',
                'lot_rebate_amount' => 10,
                'min_direct_referral' => 3,
                'min_direct_referral_rank_id' => 6,
                'min_amount_per_person' => 30000,
                'min_group_sales' => 10000000,
                'edited_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ],
        ]);
    }
}
