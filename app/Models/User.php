<?php

namespace App\Models;

use App\Domain\Concerns\HasUlidPrimaryKey;
use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\Shop;
use App\Domain\Identity\Models\Terminal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasUlidPrimaryKey;
    use Notifiable;
    use SoftDeletes;
    use TwoFactorAuthenticatable;

    /**
     * @var string[]
     */
    protected $fillable = [
        'shop_id',
        'role_id',
        'login_id',
        'name',
        'email',
        'phone',
        'password',
        'pin_hash',
        'is_active',
        'origin_terminal_id',
        'last_login_at',
    ];

    /**
     * @var array
     */
    protected $hidden = [
        'password',
        'pin_hash',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function originTerminal(): BelongsTo
    {
        return $this->belongsTo(Terminal::class, 'origin_terminal_id');
    }

    public function canViewCost(): bool
    {
        $this->loadMissing('role');

        return (bool) optional($this->role)->can_view_cost;
    }

    public function hasRole(string ...$names): bool
    {
        $this->loadMissing('role');

        return in_array(optional($this->role)->name, $names, true);
    }

    public function roleName(): ?string
    {
        $this->loadMissing('role');

        return optional($this->role)->name;
    }

    public function isStaff(): bool
    {
        return $this->hasRole('owner', 'manager', 'cashier');
    }
}
