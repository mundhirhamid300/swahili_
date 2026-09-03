<?php

/** Hii request huhakikisha data ya fomu ni sahihi kabla haijatumika. */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChatbotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:500'],
            'mode' => ['nullable', 'in:tutor,translate,grammar,vocabulary,sentence'],
            'lesson_id' => ['nullable', 'integer', 'exists:lessons,id'],
            'conversation_id' => ['nullable', 'uuid'],
        ];
    }
}
