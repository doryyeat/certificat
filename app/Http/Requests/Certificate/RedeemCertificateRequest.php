<?php

namespace App\Http\Requests\Certificate;

use Illuminate\Foundation\Http\FormRequest;

class RedeemCertificateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->business;
    }

    public function rules(): array
    {
        return [
            'qr_data' => ['required_without:pin_code', 'string'],
            'pin_code' => ['required_without:qr_data', 'string', 'size:6'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'amount' => ['required', 'numeric', 'min:1'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Проверяем, что локация принадлежит бизнесу
            if ($this->location_id) {
                $location = Location::find($this->location_id);
                if ($location && $location->business_id !== $this->user()->business->id) {
                    $validator->errors()->add('location_id', 'Локация не принадлежит вашему бизнесу');
                }
            }
        });
    }
}
