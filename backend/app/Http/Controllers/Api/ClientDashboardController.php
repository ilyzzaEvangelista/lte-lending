<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoanDetail;
use App\Models\LoanPayment;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientDashboardController extends Controller
{
    public function dashboard(Request $request): JsonResponse
    {
        /** @var User $customer */
        $customer = $request->user();

        $all = $customer->loanDetails()->latest()->get();

        $counts = [
            'applications' => $all->count(),
            'pending' => $all->where('status', LoanDetail::STATUS_PENDING)->count(),
            'approved_or_active' => $all->whereIn('status', [LoanDetail::STATUS_APPROVED, LoanDetail::STATUS_ACTIVE])->count(),
        ];

        $upcoming = [];
        foreach ($all as $loan) {
            if (! in_array($loan->status, [LoanDetail::STATUS_APPROVED, LoanDetail::STATUS_ACTIVE], true)) {
                continue;
            }
            $records = $loan->loanRecords()->orderBy('id')->get();
            $lastBalance = $records->isNotEmpty() ? (float) $records->last()->balance : null;

            $upcoming[] = [
                'id' => $loan->id,
                'transaction_no' => $loan->transaction_no,
                'status' => $loan->status,
                'amount' => $loan->amount,
                'monthly' => $loan->monthly,
                'balance_outstanding' => $lastBalance,
                'next_payment_due' => $this->computeNextPaymentDue($loan),
            ];
        }

        $upcoming = collect($upcoming)
            ->sortBy(fn (array $r) => $r['next_payment_due'] ?? '9999-12-31')
            ->values()
            ->all();

        $soonest = null;
        foreach ($upcoming as $row) {
            if ($row['next_payment_due'] !== null) {
                $soonest = $row;
                break;
            }
        }

        return response()->json([
            'data' => [
                'display_name' => $customer->fullName() ?: $customer->username,
                'email' => $customer->email,
                'counts' => $counts,
                'upcoming_loans' => $upcoming,
                'soonest_payment' => $soonest,
            ],
        ]);
    }

    /**
     * Next installment date on a fixed monthly cadence from the first loan_record date,
     * with optional +1 month when a payment is pending and today is on/after the current period due.
     */
    private function computeNextPaymentDue(LoanDetail $d): ?string
    {
        $records = $d->loanRecords()->orderBy('id')->get();
        if ($records->isEmpty()) {
            return null;
        }
        $first = $records->first();
        $last = $records->last();
        if ((float) $last->balance <= 0) {
            return null;
        }
        $anchor = Carbon::parse($first->payment_date)->startOfDay();
        $n = (int) $last->no_of_payments;
        $currentPeriodDue = $anchor->copy()->addMonths($n + 1);
        $hasPending = $d->loanPayments()
            ->where('status', LoanPayment::STATUS_PENDING)
            ->exists();
        $today = Carbon::now()->startOfDay();
        $pendingShift = $hasPending && $today->greaterThanOrEqualTo($currentPeriodDue);
        $monthsAhead = $n + 1 + ($pendingShift ? 1 : 0);

        return $anchor->copy()->addMonths($monthsAhead)->toDateString();
    }
}
