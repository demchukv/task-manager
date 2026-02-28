<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'Будь ласка, введіть вашу електронну пошту.',
            'email.email' => 'Введіть коректну адресу електронної пошти.',
            'password.required' => 'Пароль є обов\'язковим для входу.',
        ];
    }
}
