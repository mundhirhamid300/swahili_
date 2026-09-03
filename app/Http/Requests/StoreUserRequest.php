<?php

/** Hii request huhakikisha data ya fomu ni sahihi kabla haijatumika. */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isSuperAdmin();
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;
        $emailRules = ['required', 'email', 'max:255'];

        if ($userId) {
            $emailRules[] = 'unique:users,email,'.$userId;
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => $emailRules,
            'password' => [$userId ? 'nullable' : 'required', 'confirmed', Password::defaults()],
            'role' => ['required', 'in:admin'],
            'status' => ['required', 'in:pending,active,inactive'],
            'country' => ['nullable', 'string', 'max:100'],
            'learning_level' => ['nullable', 'in:beginner,intermediate'],
        ];
    }
}
