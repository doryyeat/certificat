<?php

namespace App\Http\Requests\Certificate;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateCertificateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->business;
    }

    public function rules(): array
    {
        return [
            'segment_id' => ['required', 'exists:segments,id'],
            'location_id' => ['required', 'exists:locations,id',
                Rule::exists('locations', 'id')->where('business_id', $this->user()->business->id)
            ],
            'nominal' => ['required', 'numeric', 'min:100', 'max:100000'],
            'expires_days' => ['sometimes', 'integer', 'min:1', 'max:365'],
        ];
    }

    public function messages(): array
    {
        return [
            'nominal.min' => 'Минимальная сумма сертификата 100 BYN',
            'nominal.max' => 'Максимальная сумма сертификата 100 000 BYN',
            'location_id.exists' => 'Локация не принадлежит вашему бизнесу',
        ];
    }
}
