<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\LoanDetail;
use App\Models\LoanPayment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        $statuses = [
            LoanDetail::STATUS_PENDING,
            LoanDetail::STATUS_APPROVED,
            LoanDetail::STATUS_REJECTED,
            LoanDetail::STATUS_ACTIVE,
            LoanDetail::STATUS_CLOSED,
        ];

        $loanCounts = LoanDetail::query()
            ->select('status', DB::raw('count(*) as c'))
            ->groupBy('status')
            ->pluck('c', 'status');

        $loans_by_status = [];
        foreach ($statuses as $s) {
            $loans_by_status[$s] = (int) ($loanCounts[$s] ?? 0);
        }

        $total_loans = array_sum($loans_by_status);

        $portfolio_exposure = (float) LoanDetail::query()
            ->whereIn('status', [LoanDetail::STATUS_APPROVED, LoanDetail::STATUS_ACTIVE])
            ->sum('amount');

        $payments_pending = LoanPayment::query()->where('status', LoanPayment::STATUS_PENDING)->count();
        $payments_confirmed = LoanPayment::query()->where('status', LoanPayment::STATUS_CONFIRMED)->count();
        $payments_rejected = LoanPayment::query()->where('status', LoanPayment::STATUS_REJECTED)->count();

        $activity_last_7_days = ActivityLog::query()
            ->where('logged_at', '>=', now()->subDays(7))
            ->count();

        $customers_count = User::query()->where('role', User::ROLE_CLIENT)->count();

        return response()->json([
            'data' => [
                'total_loans' => $total_loans,
                'loans_by_status' => $loans_by_status,
                'loans_pending_review' => $loans_by_status[LoanDetail::STATUS_PENDING],
                'portfolio_exposure' => round($portfolio_exposure, 2),
                'payments_pending' => $payments_pending,
                'payments_confirmed' => $payments_confirmed,
                'payments_rejected' => $payments_rejected,
                'payments_total' => $payments_pending + $payments_confirmed + $payments_rejected,
                'customers_count' => $customers_count,
                'activity_last_7_days' => $activity_last_7_days,
            ],
        ]);
    }
}
