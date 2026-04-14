<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoanDetail extends Model
{
    use SoftDeletes;

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
        'date_of_birth',
        'age',
        'gender',
        'email',
        'address',
        'city',
        'country',
        'nearest_branch',
        'phone',
        'profession',
        'loan_type',
        'monthly_income_other',
        'has_existing_loan',
        'terms_accepted_at',
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
            'date_of_birth' => 'date',
            'age' => 'integer',
            'has_existing_loan' => 'boolean',
            'terms_accepted_at' => 'datetime',
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
