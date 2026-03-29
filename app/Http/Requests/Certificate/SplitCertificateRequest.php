<?php

namespace App\Http\Requests\Certificate;

use Illuminate\Foundation\Http\FormRequest;

class SplitCertificateRequest extends FormRequest
{
    public function authorize(): bool
    {
        $certificate = $this->route('certificate');

        // Проверяем, что сертификат принадлежит бизнесу пользователя
        return $certificate &&
            $certificate->business_id === $this->user()->business->id &&
            $certificate->status === 'active';
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:100', 'max:' . $this->certificate->balance],
            'recipients' => ['required', 'array', 'min:1', 'max:5'],
            'recipients.*.email' => ['required', 'email', 'max:255'],
            'recipients.*.name' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.max' => 'Сумма разделения не может превышать баланс сертификата',
            'recipients.max' => 'Максимум 5 получателей за одно разделение',
        ];
    }
}
