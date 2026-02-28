<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|required|in:todo,in_progress,done',
            'priority' => 'sometimes|required|in:low,medium,high',
            'assignee_id' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Назва завдання не може бути порожньою.',
            'status.required' => 'Статус завдання є обов\'язковим.',
            'priority.required' => 'Пріоритет завдання є обов\'язковим.',
            'status.in' => 'Вибрано некоректний статус.',
            'priority.in' => 'Вибрано некоректний пріоритет.',
            'assignee_id.exists' => 'Вибраного користувача не існує.',
        ];
    }
}
