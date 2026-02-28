<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Назва проекту є обов\'язковою.',
            'name.string' => 'Назва проекту має бути рядком.',
            'name.max' => 'Назва проекту не повинна перевищувати 255 символів.',
        ];
    }
}
