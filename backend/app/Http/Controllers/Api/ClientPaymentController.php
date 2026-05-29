<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoanDetail;
use App\Models\LoanPayment;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClientPaymentController extends Controller
{
    public function store(Request $request, LoanDetail $loanDetail): JsonResponse
    {
        /** @var User $customer */
        $customer = $request->user();

        if ($loanDetail->user_id !== $customer->id) {
            abort(404);
        }

        if (! in_array($loanDetail->status, [LoanDetail::STATUS_APPROVED, LoanDetail::STATUS_ACTIVE], true)) {
            return response()->json(['message' => 'Loan must be approved or active to submit payments.'], 422);
        }

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'receipt_base64' => ['nullable', 'string'],
        ]);

        $receipt = null;
        if (! empty($data['receipt_base64'])) {
            $raw = preg_replace('#^data:image/[\w+]+;base64,#i', '', $data['receipt_base64']);
            $receipt = base64_decode($raw, true) ?: null;
        }

        $payment = LoanPayment::create([
            'transaction_no' => $loanDetail->transaction_no,
            'user_id' => $customer->id,
            'full_name' => $customer->fullName(),
            'amount' => $data['amount'],
            'balance' => null,
            'receipt_image' => $receipt,
            'status' => LoanPayment::STATUS_PENDING,
        ]);

        ActivityLogger::record($customer, 'Loan payment submitted', $customer->username);

        $out = $payment->toArray();
        $out['has_receipt'] = $payment->receipt_image !== null;
        unset($out['receipt_image']);

        return response()->json(['data' => $out], 201);
    }

    public function receipt(Request $request, LoanPayment $loanPayment): Response
    {
        /** @var User $customer */
        $customer = $request->user();
        if ($loanPayment->user_id !== $customer->id) {
            abort(404);
        }

        $binary = $loanPayment->getRawOriginal('receipt_image');
        if ($binary === null || $binary === '') {
            abort(404);
        }

        return response($binary, 200, [
            'Content-Type' => $this->guessImageMimeType($binary),
            'Cache-Control' => 'private, max-age=3600',
        ]);
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
