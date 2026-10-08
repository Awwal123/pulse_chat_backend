<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string'],

            'email' => [
                'required_if:purpose,register',
                'nullable',
                'email',
            ],

            'purpose' => ['required', 'in:register,login'],
        ];
    }
}