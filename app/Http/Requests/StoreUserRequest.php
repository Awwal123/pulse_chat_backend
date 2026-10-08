<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
  public function rules(): array
{
    return [
        'phone' => ['required', 'string'],
        'email' => ['required', 'email', 'unique:users,email'],
        'name' => ['required', 'string', 'max:255'],
        'profile_picture' => ['nullable', 'string'],
        'security_pin' => ['nullable', 'digits:4'],
    ];
}
}