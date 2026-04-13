<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'last_name',
        'first_name',
        'profile_image',
        'address',
        'email',
        'contact',
        'username',
        'password',
        'status',
    ];

    protected $hidden = [
        'password',
        'profile_image',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'status' => 'integer',
        ];
    }

    public function loanDetails(): HasMany
    {
        return $this->hasMany(LoanDetail::class);
    }

    public function loanRecords(): HasMany
    {
        return $this->hasMany(LoanRecord::class);
    }

    public function loanPayments(): HasMany
    {
        return $this->hasMany(LoanPayment::class);
    }

    public function fullName(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }
}
