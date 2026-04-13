<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoanPayment extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'transaction_no',
        'customer_id',
        'full_name',
        'amount',
        'balance',
        'receipt_image',
        'status',
        'confirmed_by',
    ];

    protected $hidden = [
        'receipt_image',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'balance' => 'decimal:2',
        ];
    }

    public function loanDetail(): BelongsTo
    {
        return $this->belongsTo(LoanDetail::class, 'transaction_no', 'transaction_no');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function confirmedByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'confirmed_by');
    }
}
