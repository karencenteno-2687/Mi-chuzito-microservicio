<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VerificationCode extends Model
{
    protected $fillable = [
        'user_id',
        'code',
        'type',
        'channel',
        'attempts',
        'max_attempts',
        'used',
        'expires_at',
        'resend_after',
    ];

    protected $casts = [
        'used'         => 'boolean',
        'expires_at'   => 'datetime',
        'resend_after' => 'datetime',
        'attempts'     => 'integer',
        'max_attempts' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Verificar si el codigo es valido
    public function isValid(string $code): bool
    {
        if ($this->used) return false;
        if ($this->attempts >= $this->max_attempts) return false;
        if (now()->gt($this->expires_at)) return false;
        return $this->code === $code;
    }

    // Verificar si puede reenviar
    public function canResend(): bool
    {
        if (!$this->resend_after) return true;
        return now()->gt($this->resend_after);
    }

    // Segundos para poder reenviar
    public function secondsUntilResend(): int
    {
        if ($this->canResend()) return 0;
        return (int) now()->diffInSeconds($this->resend_after);
    }

    // Generar nuevo codigo para un usuario
    public static function generate(User $user, string $type, string $channel, int $expiresMinutes = 10): self
    {
        // Invalidar codigos anteriores del mismo tipo
        self::where('user_id', $user->id)
            ->where('type', $type)
            ->where('used', false)
            ->update(['used' => true]);

        return self::create([
            'user_id'      => $user->id,
            'code'         => str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT),
            'type'         => $type,
            'channel'      => $channel,
            'attempts'     => 0,
            'max_attempts' => 5,
            'used'         => false,
            'expires_at'   => now()->addMinutes($expiresMinutes),
            'resend_after' => now()->addSeconds(60),
        ]);
    }
}