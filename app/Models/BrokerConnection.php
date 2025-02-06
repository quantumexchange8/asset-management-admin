<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BrokerConnection extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'broker_id',
        'broker_login',
        'capital_fund',
        'connection_type',
        'connection_number',
        'joined_at',
        'removed_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'joined_at' => 'datetime',
            'removed_at' => 'datetime',
        ];
    }

    // Relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function broker(): BelongsTo
    {
        return $this->belongsTo(Broker::class, 'broker_id', 'id');
    }
}
