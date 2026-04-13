<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoanDetail extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'transaction_no',
        'customer_id',
        'first_name',
        'last_name',
        'email',
        'address',
        'amount',
        'interest',
        'monthly',
        'tenure',
        'purpose',
        'payslip_image',
        'status',
        'approved_by',
        'admin_note',
    ];

    protected $hidden = [
        'payslip_image',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'interest' => 'decimal:4',
            'monthly' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function approvedByAdmin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'approved_by');
    }

    public function loanRecords(): HasMany
    {
        return $this->hasMany(LoanRecord::class, 'transaction_no', 'transaction_no');
    }

    public function loanPayments(): HasMany
    {
        return $this->hasMany(LoanPayment::class, 'transaction_no', 'transaction_no');
    }
}
