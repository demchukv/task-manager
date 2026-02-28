<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:todo,in_progress,done',
            'priority' => 'nullable|in:low,medium,high',
            'assignee_id' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Назва завдання є обов\'язковою.',
            'title.max' => 'Назва завдання не повинна перевищувати 255 символів.',
            'status.in' => 'Вибрано некоректний статус завдання.',
            'priority.in' => 'Вибрано некоректний пріоритет завдання.',
            'assignee_id.exists' => 'Вибраного користувача не існує.',
            'due_date.date' => 'Введіть коректну дату дедлайну.',
        ];
    }
}
