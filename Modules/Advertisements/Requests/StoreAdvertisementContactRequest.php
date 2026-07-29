<?php

namespace Modules\Advertisements\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdvertisementContactRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Anyone can submit a contact inquiry
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'message' => ['required', 'string', 'min:10', 'max:5000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'نام شما الزامی است',
            'name.string' => 'نام باید متن باشد',
            'name.max' => 'نام نمی‌تواند بیش از ۲۵۵ کاراکتر باشد',
            'email.required' => 'ایمیل الزامی است',
            'email.email' => 'فرمت ایمیل معتبر نیست',
            'email.max' => 'ایمیل نمی‌تواند بیش از ۲۵۵ کاراکتر باشد',
            'phone.max' => 'شماره تلفن نمی‌تواند بیش از ۲۰ کاراکتر باشد',
            'message.required' => 'پیام شما الزامی است',
            'message.min' => 'پیام باید حداقل ۱۰ کاراکتر باشد',
            'message.max' => 'پیام نمی‌تواند بیش از ۵۰۰۰ کاراکتر باشد',
        ];
    }
}
