<?php

namespace Modules\KYC\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreKycIdentityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'father_name' => ['required', 'string', 'max:100'],
            'national_code' => [
                'required',
                'string',
                'regex:/^[0-9]{10}$/',
                'unique:kyc_profiles,national_code',
            ],
            'birth_date' => ['required', 'date', 'before:today'],
            'birth_place' => ['required', 'string', 'max:100'],
            'mobile_number' => ['required', 'string', 'regex:/^(0|\\+98)[0-9]{10}$/'],
            'postal_code' => ['required', 'string', 'regex:/^[0-9]{10}$/'],
            'address' => ['required', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'last_name.required' => 'Last name is required.',
            'father_name.required' => 'Father\'s name is required.',
            'national_code.required' => 'National code is required.',
            'national_code.regex' => 'National code must be 10 digits.',
            'national_code.unique' => 'This national code is already registered.',
            'birth_date.required' => 'Birth date is required.',
            'birth_date.before' => 'Birth date must be in the past.',
            'birth_place.required' => 'Birth place is required.',
            'mobile_number.required' => 'Mobile number is required.',
            'mobile_number.regex' => 'Mobile number format is invalid.',
            'postal_code.required' => 'Postal code is required.',
            'postal_code.regex' => 'Postal code must be 10 digits.',
            'address.required' => 'Address is required.',
        ];
    }
}
