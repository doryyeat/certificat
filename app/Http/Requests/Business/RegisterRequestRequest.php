<?php

namespace App\Http\Requests\Business;

use App\Models\RegisterRequest;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:200'],
            'form_of_own' => ['required', 'string', 'max:100'],
            'contact' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', ],
            'address' => ['required', 'string', 'max:300'],
            'bank_info' => ['required', 'string', 'max:500'],
            'unp' => [
                'required',
                'string',
            ],
            'email' => [
                'required',
                'email',
            ],
            'password' => ['required'],
        ];
    }
}
