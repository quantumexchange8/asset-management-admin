<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rank extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'rank_name',
        'rank_position',
        'lot_rebate_currency',
        'lot_rebate_amount',
        'min_direct_referral',
        'min_direct_referral_rank_id',
        'min_amount_per_person',
        'min_group_sales',
        'edited_by',
    ];
}
