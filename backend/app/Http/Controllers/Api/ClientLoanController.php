<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLoanApplicationRequest;
use App\Models\LoanDetail;
use App\Models\User;
use App\Support\ActivityLogger;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class ClientLoanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var User $customer */
        $customer = $request->user();

        $rows = $customer->loanDetails()
            ->latest()
            ->get()
            ->map(fn (LoanDetail $d) => $this->serializeDetail($d));

        return response()->json(['data' => $rows]);
    }

    public function store(StoreLoanApplicationRequest $request): JsonResponse
    {
        /** @var User $customer */
        $customer = $request->user();

        $hasActiveLoan = $customer->loanDetails()
            ->where('status', LoanDetail::STATUS_ACTIVE)
            ->exists();
        if ($hasActiveLoan) {
            throw ValidationException::withMessages([
                'loan' => ['You already have an active loan. Close it before submitting a new application.'],
            ]);
        }

        $validated = $request->validated();

        $this->assertProfileCompleteForApplication($customer);

        $transactionNo = 'TRN-'.strtoupper(Str::random(12));

        $raw = preg_replace('#^data:image/[\w+]+;base64,#i', '', $validated['payslip_base64']);
        $payslip = base64_decode($raw, true);
        if ($payslip === false || $payslip === '') {
            throw ValidationException::withMessages([
                'payslip_base64' => ['Please upload a valid payslip image.'],
            ]);
        }

        $dateOfBirth = $customer->age !== null
            ? Carbon::today()->subYears((int) $customer->age)->toDateString()
            : null;

        $payload = collect($validated)->except(['payslip_base64', 'accept_terms'])->merge([
            'first_name' => (string) $customer->first_name,
            'last_name' => (string) $customer->last_name,
            'date_of_birth' => $dateOfBirth,
            'age' => $customer->age,
            'gender' => $customer->gender,
            'email' => (string) $customer->email,
            'address' => $customer->address,
            'city' => null,
            'country' => null,
            'nearest_branch' => null,
            'phone' => $customer->contact !== null ? (string) $customer->contact : '',
            'profession' => null,
            'transaction_no' => $transactionNo,
            'user_id' => $customer->id,
            'interest' => 0,
            'monthly' => null,
            'terms_accepted_at' => now(),
            'payslip_image' => $payslip,
            'status' => LoanDetail::STATUS_PENDING,
        ])->all();

        $detail = LoanDetail::create($payload);

        ActivityLogger::record($customer, 'Loan application submitted', $customer->username);

        return response()->json(['data' => $this->serializeDetail($detail)], 201);
    }

    public function show(Request $request, LoanDetail $loanDetail): JsonResponse
    {
        /** @var User $customer */
        $customer = $request->user();
        if ($loanDetail->user_id !== $customer->id) {
            abort(404);
        }

        return response()->json(['data' => $this->serializeDetail($loanDetail, true)]);
    }

    public function records(Request $request, LoanDetail $loanDetail): JsonResponse
    {
        /** @var User $customer */
        $customer = $request->user();
        if ($loanDetail->user_id !== $customer->id) {
            abort(404);
        }

        $records = $loanDetail->loanRecords()->orderBy('id')->get();

        return response()->json(['data' => $records]);
    }

    public function payslip(Request $request, LoanDetail $loanDetail): Response
    {
        /** @var User $customer */
        $customer = $request->user();
        if ($loanDetail->user_id !== $customer->id) {
            abort(404);
        }

        $binary = $loanDetail->getRawOriginal('payslip_image');
        if ($binary === null || $binary === '') {
            abort(404);
        }

        return response($binary, 200, [
            'Content-Type' => $this->guessImageMimeType($binary),
            'Cache-Control' => 'private, max-age=3600',
        ]);
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

    private function assertProfileCompleteForApplication(User $user): void
    {
        $missing = [];
        if (trim((string) $user->first_name) === '') {
            $missing[] = 'first name';
        }
        if (trim((string) $user->last_name) === '') {
            $missing[] = 'last name';
        }
        if (trim((string) $user->email) === '') {
            $missing[] = 'email';
        }
        if ($user->age === null) {
            $missing[] = 'age';
        }
        if ($user->gender === null || trim((string) $user->gender) === '') {
            $missing[] = 'gender';
        }

        if ($missing !== []) {
            throw ValidationException::withMessages([
                'profile' => [
                    'Your account profile is incomplete ('.implode(', ', $missing).'). Please update your details before applying.',
                ],
            ]);
        }
    }
}
