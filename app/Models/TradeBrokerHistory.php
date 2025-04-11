<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TradeBrokerHistory extends Model
{
    use SoftDeletes;

    protected $table = 'trade_broker_histories';

    protected $guarded = [
        'id', 
    ];

    protected $fillable = [
        'id',
        'email',
        'broker_id',
        'broker_login',
        'volume',
        'status',
        'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function broker(): BelongsTo
    {
        return $this->belongsTo(Broker::class, 'broker_id', 'id');
    }
}
