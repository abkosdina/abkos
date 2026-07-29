<?php

namespace Modules\Advertisements\Http\Requests;

use App\Models\City;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdvertisementRequest extends FormRequest
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

        if (is_array($loanOffer) && $this->loanOfferHasValue($loanOffer) === false) {
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
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'short_description' => 'sometimes|nullable|string',
            'province_id' => ['sometimes', 'required', 'integer', Rule::exists('provinces', 'id')],
            'city_id' => ['sometimes', 'required', 'integer', Rule::exists('cities', 'id')],
            'visibility' => 'sometimes|required|string|in:Public,Private,Hidden',
            'priority' => 'sometimes|required|integer',
            'loan_offer' => 'sometimes|nullable|array',
            'loan_offer.bank_id' => 'sometimes|nullable|integer',
            'loan_offer.loan_plan_id' => 'sometimes|nullable|integer',
            'loan_offer.loan_amount' => 'sometimes|nullable|numeric',
            'loan_offer.sale_price' => 'sometimes|nullable|numeric',
            'loan_offer.installment_count' => 'sometimes|nullable|integer',
            'loan_offer.monthly_installment' => 'sometimes|nullable|numeric',
            'loan_offer.loan_type_id' => 'sometimes|nullable|integer',
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
                        $validator->errors()->add("loan_offer.{$field}", "The {$field} field is required when loan_offer is present.");
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
                $validator->errors()->add('city_id', 'The selected city does not belong to the selected province.');
            }
        });
    }
}
