<?php

/** Hii request huhakikisha data ya fomu ni sahihi kabla haijatumika. */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreLessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'lesson_order' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:draft,published'],
            'image' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
