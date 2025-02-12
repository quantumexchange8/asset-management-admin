<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TradeRebateSummary extends Model
{
    use SoftDeletes;

    // Relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'upline_user_id', 'id');
    }

    public function subject_user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'downline_user_id', 'id');
    }

    public function broker(): BelongsTo
    {
        return $this->belongsTo(Broker::class, 'broker_id', 'id');
    }
}
