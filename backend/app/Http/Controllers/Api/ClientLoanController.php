<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\LoanDetail;
use App\Support\ActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClientLoanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $request->user();

        $rows = $customer->loanDetails()
            ->latest()
            ->get()
            ->map(fn (LoanDetail $d) => $this->serializeDetail($d));

        return response()->json(['data' => $rows]);
    }

    public function store(Request $request): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $request->user();

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:100', 'max:99999999.99'],
            'tenure' => ['required', 'integer', 'min:1', 'max:360'],
            'purpose' => ['nullable', 'string', 'max:255'],
            'payslip_base64' => ['nullable', 'string'],
        ]);

        $transactionNo = 'TRN-'.strtoupper(Str::random(12));

        $payslip = null;
        if (! empty($data['payslip_base64'])) {
            $raw = preg_replace('#^data:image/[\w+]+;base64,#i', '', $data['payslip_base64']);
            $payslip = base64_decode($raw, true) ?: null;
        }

        $detail = LoanDetail::create([
            'transaction_no' => $transactionNo,
            'customer_id' => $customer->id,
            'first_name' => $customer->first_name,
            'last_name' => $customer->last_name,
            'email' => $customer->email,
            'address' => $customer->address,
            'amount' => $data['amount'],
            'interest' => 0,
            'monthly' => null,
            'tenure' => $data['tenure'],
            'purpose' => $data['purpose'] ?? null,
            'payslip_image' => $payslip,
            'status' => LoanDetail::STATUS_PENDING,
        ]);

        ActivityLogger::record($customer, 'Loan application submitted', $customer->username);

        return response()->json(['data' => $this->serializeDetail($detail)], 201);
    }

    public function show(Request $request, LoanDetail $loanDetail): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $request->user();
        if ($loanDetail->customer_id !== $customer->id) {
            abort(404);
        }

        return response()->json(['data' => $this->serializeDetail($loanDetail, true)]);
    }

    public function records(Request $request, LoanDetail $loanDetail): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $request->user();
        if ($loanDetail->customer_id !== $customer->id) {
            abort(404);
        }

        $records = $loanDetail->loanRecords()->orderBy('id')->get();

        return response()->json(['data' => $records]);
    }

    private function serializeDetail(LoanDetail $d, bool $includeMeta = false): array
    {
        $base = $d->toArray();
        $base['has_payslip'] = ! empty($d->getRawOriginal('payslip_image'));

        if ($includeMeta) {
            $base['loan_records'] = $d->loanRecords()->orderBy('id')->get();
            $base['loan_payments'] = $d->loanPayments()->orderByDesc('id')->get()
                ->map(function ($p) {
                    $a = $p->toArray();
                    $a['has_receipt'] = ! empty($p->getRawOriginal('receipt_image'));

                    return $a;
                });
        }

        return $base;
    }
}
