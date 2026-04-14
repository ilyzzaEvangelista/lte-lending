<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\LoanPayment;
use App\Models\LoanRecord;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class AdminPaymentController extends Controller
{
    public function index(): JsonResponse
    {
        $rows = LoanPayment::query()
            ->with(['customer:id,first_name,last_name,email', 'loanDetail:id,transaction_no,status'])
            ->orderByDesc('id')
            ->get()
            ->map(function (LoanPayment $p) {
                $a = $p->toArray();
                $a['has_receipt'] = ! empty($p->getRawOriginal('receipt_image'));

                return $a;
            });

        return response()->json(['data' => $rows]);
    }

    public function receipt(LoanPayment $loanPayment): Response
    {
        $binary = $loanPayment->getRawOriginal('receipt_image');
        if ($binary === null || $binary === '') {
            abort(404);
        }

        return response($binary, 200, [
            'Content-Type' => $this->guessImageMimeType($binary),
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }

    public function update(Request $request, LoanPayment $loanPayment): JsonResponse
    {
        /** @var Admin $admin */
        $admin = $request->user();

        $data = $request->validate([
            'status' => ['required', 'string', 'in:pending,confirmed,rejected'],
        ]);

        DB::transaction(function () use ($loanPayment, $data, $admin): void {
            $loanPayment->status = $data['status'];

            if ($data['status'] === LoanPayment::STATUS_CONFIRMED) {
                $loanPayment->confirmed_by = $admin->id;

                $prev = LoanRecord::where('transaction_no', $loanPayment->transaction_no)
                    ->orderByDesc('id')
                    ->first();

                if ($prev) {
                    $newBalance = max(0, (float) $prev->balance - (float) $loanPayment->amount);

                    LoanRecord::create([
                        'transaction_no' => $loanPayment->transaction_no,
                        'customer_id' => $loanPayment->customer_id,
                        'no_of_payments' => $prev->no_of_payments + 1,
                        'payment_date' => now()->toDateString(),
                        'amount' => $prev->amount,
                        'interest' => $prev->interest,
                        'monthly_pay' => $prev->monthly_pay,
                        'payment' => $loanPayment->amount,
                        'balance' => $newBalance,
                    ]);

                    $loanPayment->balance = $newBalance;
                }
            }

            if ($data['status'] === LoanPayment::STATUS_REJECTED) {
                $loanPayment->confirmed_by = $admin->id;
            }

            $loanPayment->save();
        });

        ActivityLogger::record($admin, 'Payment '.$data['status'], $admin->username);

        $loanPayment->load(['customer:id,first_name,last_name,email']);

        $out = $loanPayment->toArray();
        $out['has_receipt'] = ! empty($loanPayment->getRawOriginal('receipt_image'));

        return response()->json(['data' => $out]);
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
