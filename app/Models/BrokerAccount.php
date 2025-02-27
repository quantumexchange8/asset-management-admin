<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BrokerAccount extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'broker_id',
        'broker_login',
        'broker_capital',
        'master_password',
    ];
}
