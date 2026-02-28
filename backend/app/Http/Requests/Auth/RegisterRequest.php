<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Будь ласка, введіть ваше ім\'я.',
            'email.required' => 'Електронна пошта є обов\'язковою.',
            'email.email' => 'Введіть коректну адресу електронної пошти.',
            'email.unique' => 'Ця електронна пошта вже використовується.',
            'password.required' => 'Пароль є обов\'язковим.',
            'password.min' => 'Пароль має містити мінімум 8 символів.',
            'password.confirmed' => 'Підтвердження пароля не збігається.',
        ];
    }
}
