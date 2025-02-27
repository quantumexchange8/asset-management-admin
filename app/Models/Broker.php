<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Translatable\HasTranslations;

class Broker extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia, HasTranslations;

    public array $translatable = ['description'];

    protected function casts(): array
    {
        return [
            'description' => 'json',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function connections(): HasMany
    {
        return $this->hasMany(BrokerConnection::class, 'broker_id', 'id');
    }

    public function actions(): HasMany
    {
        return $this->hasMany(BrokerAction::class, 'broker_id', 'id');
    }
}
