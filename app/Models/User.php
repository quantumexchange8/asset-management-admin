<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasMedia
{
    use HasFactory, HasRoles, SoftDeletes, Notifiable, InteractsWithMedia, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [
        'id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'kyc_requested_at' => 'datetime',
            'kyc_approval_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function setReferralId(): void
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $randomString = 'VTAx';

        $length = 10 - strlen($randomString);

        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        $this->referral_code = $randomString;
        $this->save();
    }

    public function getChildrenIds(): array
    {
        return User::query()->where('hierarchyList', 'like', '%-' . $this->id . '-%')
            ->pluck('id')
            ->toArray();
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    public function rank(): BelongsTo
    {
        return $this->belongsTo(Rank::class, 'setting_rank_id', 'id');
    }

    public function upline(): BelongsTo
    {
        return $this->belongsTo(User::class, 'upline_id', 'id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(User::class, 'upline_id', 'id');
    }

    public function wallets(): HasMany
    {
        return $this->hasMany(Wallet::class, 'user_id', 'id');
    }

    public function directs(): HasMany
    {
        return $this->hasMany(User::class, 'upline_id', 'id');
    }

    // Activity Log
    public function getActivitylogOptions(): LogOptions
    {
        $user = $this->fresh();

        return LogOptions::defaults()
            ->useLogName('user')
            ->logOnly([
                'id',
                'name',
                'username',
                'email_verified_at',
                'email',
                'password',
                'security_pin',
                'dial_code',
                'phone',
                'phone_number',
                'chinese_name',
                'dob',
                'identity_number',
                'country_id',
                'nationality',
                'register_ip',
                'last_login_ip',
                'upline_id',
                'hierarchyList',
                'referral_code',
                'id_number',
                'kyc_status',
                'kyc_requested_at',
                'kyc_approval_at',
                'kyc_approval_description',
                'gender',
                'role',
                'setting_rank_id',
                'rank_up_status',
                'status',
                'remarks',
                'remember_token',
                'password_changed_at',
            ])
            ->setDescriptionForEvent(function (string $eventName) use ($user) {
                $actorName = Auth::user() ? Auth::user()->name : 'System';
                return "{$actorName} has {$eventName} user with ID: {$user->id}";
            })
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
