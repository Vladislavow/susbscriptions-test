<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    const COMMON_USER_ROLE = 0;
    const ADMIN_ROLE = 1;

    const ROLES = [
        self::COMMON_USER_ROLE => 'Common user',
        self::ADMIN_ROLE => 'Admin',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function password(): Attribute
    {
        return Attribute::make(
            set: fn($value) => Hash::make($value),
        );
    }


    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
    }

    private function publications(): HasMany
    {
        return $this->hasMany(Publication::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ADMIN_ROLE;
    }

    public function isSubscribed(): bool
    {
        return !!$this->subscription;
    }

    public function getRemainingPublicationsCount(): int
    {
        $subscription = $this->subscription;

        if ($subscription) {
            $plan = Plan::find($subscription->plan_id);
            $publicationsCount = Publication::where('user_id', $this->id)
                ->where('status', Publication::ACTIVE_STATUS)
                ->count();

            return $plan->max_publications - $publicationsCount;
        } else {
            return 0;
        }
    }
}
