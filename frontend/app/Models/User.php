<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable
{
    use HasFactory, HasApiTokens, Notifiable, HasRoles, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'phone', 'password',
        'two_factor_enabled', 'two_factor_code', 'two_factor_expires_at',
        'is_active', 'is_verified', 'disponible', 'disponible_updated_at',
    ];

    protected $hidden = [
        'password', 'remember_token', 'two_factor_code',
    ];

    protected $casts = [
        'email_verified_at'     => 'datetime',
        'two_factor_enabled'    => 'boolean',
        'two_factor_expires_at' => 'datetime',
        'is_active'             => 'boolean',
        'is_verified'           => 'boolean',
        'disponible'            => 'boolean',
        'disponible_updated_at' => 'datetime',
    ];

    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    public function verificationCodes()
    {
        return $this->hasMany(VerificationCode::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function assignedOrders()
    {
        return $this->hasMany(Order::class, 'repartidor_id');
    }

    public function vendorOrders()
    {
        return $this->hasMany(Order::class, 'vendedor_id');
    }

    public function generateTwoFactorCode(): string
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $this->update([
            'two_factor_code'       => $code,
            'two_factor_expires_at' => now()->addMinutes(10),
        ]);
        return $code;
    }

    public function verifyTwoFactorCode(string $code): bool
    {
        return $this->two_factor_code === $code
            && $this->two_factor_expires_at
            && now()->lt($this->two_factor_expires_at);
    }

    public function clearTwoFactorCode(): void
    {
        $this->update([
            'two_factor_code'       => null,
            'two_factor_expires_at' => null,
        ]);
    }
}