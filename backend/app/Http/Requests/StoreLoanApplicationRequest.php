<?php

namespace App\Http\Requests;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;

class StoreLoanApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() instanceof Customer;
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
            'first_name' => ['required', 'string', 'max:60'],
            'last_name' => ['required', 'string', 'max:60'],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'age' => ['required', 'integer', 'min:13', 'max:120'],
            'gender' => ['required', 'string', 'in:male,female,other,prefer_not_to_say'],
            'email' => ['required', 'string', 'email', 'max:60'],
            'address' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:100'],
            'country' => ['required', 'string', 'max:100'],
            'nearest_branch' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30'],
            'profession' => ['required', 'string', 'max:120'],
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
            'first_name' => 'first name',
            'last_name' => 'last name',
            'date_of_birth' => 'date of birth',
            'nearest_branch' => 'nearest branch',
            'monthly_income_other' => 'monthly income from other sources',
            'loan_type' => 'loan type',
            'has_existing_loan' => 'existing loan',
            'accept_terms' => 'terms acceptance',
            'payslip_base64' => 'payslip',
        ];
    }
}
