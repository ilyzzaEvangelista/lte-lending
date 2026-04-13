<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\LoanDetail;
use App\Models\LoanPayment;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientPaymentController extends Controller
{
    public function store(Request $request, LoanDetail $loanDetail): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $request->user();

        if ($loanDetail->customer_id !== $customer->id) {
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
            'customer_id' => $customer->id,
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
}
