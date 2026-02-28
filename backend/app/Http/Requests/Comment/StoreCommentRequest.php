<?php

namespace App\Http\Requests\Comment;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'body' => 'required|string|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'body.required' => 'Коментар не може бути порожнім.',
            'body.min' => 'Коментар має містити хоча б один символ.',
        ];
    }
}
