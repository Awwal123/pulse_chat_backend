<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'profile_picture' => ['sometimes', 'nullable', 'string'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255', 'unique:users,email,' . $this->user()->id],
           'gender' => ['sometimes', 'nullable', 'in:male,female'],
            'birthday' => ['sometimes', 'nullable', 'date', 'before:today'],
        ];
    }
}