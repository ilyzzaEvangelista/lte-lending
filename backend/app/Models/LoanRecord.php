<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanRecord extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'transaction_no',
        'user_id',
        'no_of_payments',
        'payment_date',
        'amount',
        'interest',
        'monthly_pay',
        'payment',
        'balance',
    ];

    protected function casts(): array
    {
        return [
            'payment_date' => 'date',
            'amount' => 'decimal:2',
            'interest' => 'decimal:4',
            'monthly_pay' => 'decimal:2',
            'payment' => 'decimal:2',
            'balance' => 'decimal:2',
        ];
    }

    public function loanDetail(): BelongsTo
    {
        return $this->belongsTo(LoanDetail::class, 'transaction_no', 'transaction_no');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
