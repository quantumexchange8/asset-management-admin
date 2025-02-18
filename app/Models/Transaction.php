<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Transaction extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

    protected $table = "transactions";
    
    public $primaryKey = "id";

    protected $guarded = [];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function approval_by(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handle_by', 'id');
    }

    public function to_wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'to_wallet_id', 'id');
    }

    public function from_wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'from_wallet_id', 'id');
    }
}
