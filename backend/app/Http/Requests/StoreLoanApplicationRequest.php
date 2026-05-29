<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class StoreLoanApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $u = $this->user();

        return $u instanceof User && $u->isClient();
    }

    protected function prepareForValidation(): void
    {
        $b = $this->input('has_existing_loan');
        if (is_string($b)) {
            $this->merge([
                'has_existing_loan' => filter_var($b, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false,
            ]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'loan_type' => ['required', 'string', 'max:120'],
            'monthly_income_other' => ['required', 'string', 'max:120'],
            'has_existing_loan' => ['required', 'boolean'],
            'accept_terms' => ['required', 'accepted'],
            'amount' => ['required', 'numeric', 'min:100', 'max:99999999.99'],
            'tenure' => ['required', 'integer', 'min:1', 'max:360'],
            'purpose' => ['required', 'string', 'max:255'],
            'payslip_base64' => ['required', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'monthly_income_other' => 'monthly income from other sources',
            'loan_type' => 'loan type',
            'has_existing_loan' => 'existing loan',
            'accept_terms' => 'terms acceptance',
            'payslip_base64' => 'payslip',
        ];
    }
}
