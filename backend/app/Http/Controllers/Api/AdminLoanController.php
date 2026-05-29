<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\LoanDetail;
use App\Models\LoanRecord;
use App\Support\ActivityLogger;
use App\Support\LoanMath;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class AdminLoanController extends Controller
{
    public function payslip(LoanDetail $loanDetail): Response
    {
        $binary = $loanDetail->getRawOriginal('payslip_image');
        if ($binary === null || $binary === '') {
            abort(404);
        }

        return response($binary, 200, [
            'Content-Type' => $this->guessImageMimeType($binary),
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    public function index(): JsonResponse
    {
        $rows = LoanDetail::query()
            ->with(['customer:id,first_name,last_name,email,username', 'approvedByAdmin:id,username'])
            ->latest()
            ->get()
            ->map(fn (LoanDetail $d) => $this->serialize($d));

        return response()->json(['data' => $rows]);
    }

    public function update(Request $request, LoanDetail $loanDetail): JsonResponse
    {
        /** @var Admin $admin */
        $admin = $request->user();

        $data = $request->validate([
            'status' => ['required', 'string', 'in:pending,approved,rejected,active,closed'],
            'interest' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $interest = isset($data['interest']) ? (float) $data['interest'] : (float) $loanDetail->interest;

        DB::transaction(function () use ($loanDetail, $data, $interest, $admin): void {
            $loanDetail->status = $data['status'];
            $loanDetail->admin_note = $data['admin_note'] ?? $loanDetail->admin_note;

            if (in_array($data['status'], [LoanDetail::STATUS_APPROVED, LoanDetail::STATUS_ACTIVE], true)) {
                $loanDetail->interest = $interest;
                $monthly = LoanMath::monthlyPayment((float) $loanDetail->amount, $interest, (int) $loanDetail->tenure);
                $loanDetail->monthly = $monthly;
                $loanDetail->approved_by = $admin->id;
            }

            if ($data['status'] === LoanDetail::STATUS_REJECTED) {
                $loanDetail->approved_by = $admin->id;
            }

            $loanDetail->save();

            if (in_array($loanDetail->status, [LoanDetail::STATUS_APPROVED, LoanDetail::STATUS_ACTIVE], true)) {
                $exists = LoanRecord::where('transaction_no', $loanDetail->transaction_no)->exists();
                if (! $exists) {
                    $monthly = (float) $loanDetail->monthly;
                    $tenure = (int) $loanDetail->tenure;
                    $total = LoanMath::totalRepayable($monthly, $tenure);

                    LoanRecord::create([
                        'transaction_no' => $loanDetail->transaction_no,
                        'user_id' => $loanDetail->user_id,
                        'no_of_payments' => 0,
                        'payment_date' => now()->toDateString(),
                        'amount' => $loanDetail->amount,
                        'interest' => $loanDetail->interest,
                        'monthly_pay' => $monthly,
                        'payment' => 0,
                        'balance' => $total,
                    ]);
                }
            }
        });

        ActivityLogger::record($admin, 'Loan detail updated', $admin->username);

        $loanDetail->load(['customer:id,first_name,last_name,email,username', 'approvedByAdmin:id,username']);

        return response()->json(['data' => $this->serialize($loanDetail)]);
    }

    private function serialize(LoanDetail $d): array
    {
        $base = $d->toArray();
        $base['has_payslip'] = ! empty($d->getRawOriginal('payslip_image'));

        return $base;
    }

    private function guessImageMimeType(string $binary): string
    {
        return match (true) {
            str_starts_with($binary, "\xFF\xD8\xFF") => 'image/jpeg',
            str_starts_with($binary, "\x89PNG\r\n\x1A\n") => 'image/png',
            str_starts_with($binary, 'GIF87a') || str_starts_with($binary, 'GIF89a') => 'image/gif',
            str_starts_with($binary, 'RIFF') && strlen($binary) >= 12 && substr($binary, 8, 4) === 'WEBP' => 'image/webp',
            default => 'application/octet-stream',
        };
    }
}
