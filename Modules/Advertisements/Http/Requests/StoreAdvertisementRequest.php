<?php

namespace Modules\Advertisements\Http\Requests;

use App\Models\City;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAdvertisementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation(): void
    {
        if (! $this->has('loan_offer')) {
            return;
        }

        $loanOffer = $this->input('loan_offer', []);

        foreach (['loan_amount', 'sale_price'] as $field) {
            if (array_key_exists($field, $loanOffer)) {
                $loanOffer[$field] = $this->normalizeMoneyField($loanOffer[$field]);
            }
        }

        if (is_array($loanOffer) && ! $this->loanOfferHasValue($loanOffer)) {
            $loanOffer = null;
        }

        $this->merge(['loan_offer' => $loanOffer]);
    }

    protected function normalizeMoneyField($value): string
    {
        if ($value === null) {
            return '';
        }

        return preg_replace('/[^0-9\.\-]/', '', (string) $value);
    }

    protected function loanOfferHasValue($loanOffer): bool
    {
        return is_array($loanOffer) && collect($loanOffer)->filter(function ($value) {
            return $value !== null && $value !== '' && !(is_array($value) && empty($value));
        })->isNotEmpty();
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'short_description' => 'nullable|string',
            'province_id' => ['required', 'integer', Rule::exists('provinces', 'id')],
            'city_id' => ['required', 'integer', Rule::exists('cities', 'id')],
            'visibility' => 'nullable|string|in:Public,Private,Hidden',
            'priority' => 'nullable|integer',
            'loan_offer' => 'nullable|array',
            'loan_offer.bank_id' => 'nullable|integer',
            'loan_offer.loan_plan_id' => 'nullable|integer',
            'loan_offer.loan_amount' => 'nullable|numeric',
            'loan_offer.sale_price' => 'nullable|numeric',
            'loan_offer.installment_count' => 'nullable|integer',
            'loan_offer.monthly_installment' => 'nullable|numeric',
            'loan_offer.loan_type_id' => 'nullable|integer',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $loanOffer = $this->input('loan_offer', []);

            if ($this->loanOfferHasValue($loanOffer)) {
                foreach ([
                    'bank_id' => 'integer',
                    'loan_plan_id' => 'integer',
                    'loan_amount' => 'numeric',
                    'sale_price' => 'numeric',
                    'installment_count' => 'integer',
                    'monthly_installment' => 'numeric',
                    'loan_type_id' => 'integer',
                ] as $field => $type) {
                    if (! array_key_exists($field, $loanOffer) || $loanOffer[$field] === null || $loanOffer[$field] === '') {
                        $validator->errors()->add("loan_offer.{$field}", "فیلد {$field} زمانی که loan offer وجود دارد، الزامی است.");
                    }
                }
            }

            $provinceId = $this->input('province_id');
            $cityId = $this->input('city_id');

            if (! $provinceId || ! $cityId) {
                return;
            }

            $city = City::query()->find($cityId);
            if (! $city) {
                return;
            }

            if ((int) $city->province_id !== (int) $provinceId) {
                $validator->errors()->add('city_id', 'شهر انتخاب‌شده با استان انتخاب‌شده هم‌خوانی ندارد.');
            }
        });
    }
}

