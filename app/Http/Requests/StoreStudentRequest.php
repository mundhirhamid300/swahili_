<?php

/** Hii request huhakikisha data ya fomu ni sahihi kabla haijatumika. */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreStudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $studentId = $this->route('student')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$studentId],
            'password' => [$studentId ? 'nullable' : 'required', 'confirmed', Password::defaults()],
            'status' => ['required', 'in:active,inactive'],
            'country' => ['nullable', 'string', 'max:100'],
            'learning_level' => ['nullable', 'in:beginner,intermediate'],
        ];
    }
}
