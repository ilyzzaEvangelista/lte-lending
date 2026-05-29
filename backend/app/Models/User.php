<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    public const ROLE_CLIENT = 'client';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'first_name',
        'last_name',
        'username',
        'contact',
        'address',
        'age',
        'gender',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'status' => 'integer',
            'age' => 'integer',
        ];
    }

    public function isClient(): bool
    {
        return $this->role === self::ROLE_CLIENT;
    }

    public function loanDetails(): HasMany
    {
        return $this->hasMany(LoanDetail::class, 'user_id');
    }

    public function loanRecords(): HasMany
    {
        return $this->hasMany(LoanRecord::class, 'user_id');
    }

    public function loanPayments(): HasMany
    {
        return $this->hasMany(LoanPayment::class, 'user_id');
    }

    public function fullName(): string
    {
        $n = trim((string) $this->first_name.' '.(string) $this->last_name);

        return $n !== '' ? $n : (string) $this->name;
    }
}
