<?php

namespace Modules\Authentication\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mobile' => ['required', 'string', 'regex:/^09\d{9}$/', 'max:11'],
            'password' => ['nullable', 'string', 'min:8'],
            'otp' => ['nullable', 'string', 'size:5'],
        ];
    }

    public function messages(): array
    {
        return [
            'mobile.required' => 'شماره همراه الزامی است.',
            'mobile.regex' => 'شماره همراه باید با 09 شروع شود و 11 رقم باشد.',
            'password.min' => 'رمز عبور باید حداقل 8 کاراکتر باشد.',
            'otp.size' => 'کد تایید باید 5 رقمی باشد.',
        ];
    }
}
