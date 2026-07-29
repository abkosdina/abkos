<?php

namespace Modules\Authentication\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'min:3', 'max:100'],
            'mobile' => ['required', 'string', 'regex:/^09\d{9}$/', 'max:11', 'unique:users,mobile'],
            'email' => ['nullable', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'نام و نام خانوادگی الزامی است.',
            'full_name.min' => 'نام و نام خانوادگی باید حداقل 3 کاراکتر باشد.',
            'mobile.required' => 'شماره همراه الزامی است.',
            'mobile.regex' => 'شماره همراه باید با 09 شروع شود و 11 رقم باشد.',
            'mobile.unique' => 'این شماره همراه قبلاً ثبت شده است.',
            'email.email' => 'ایمیل وارد شده معتبر نیست.',
            'password.required' => 'رمز عبور الزامی است.',
            'password.min' => 'رمز عبور باید حداقل 8 کاراکتر باشد.',
            'password.confirmed' => 'تأیید رمز عبور با رمز عبور مطابقت ندارد.',
        ];
    }
}
