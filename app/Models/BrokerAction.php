<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BrokerAction extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'broker_id',
        'broker_action',
        'action_url',
    ];
}
