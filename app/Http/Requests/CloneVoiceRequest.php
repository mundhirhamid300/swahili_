<?php

/** Hii request huhakikisha data ya fomu ni sahihi kabla haijatumika. */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CloneVoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:100'],
            'samples' => ['required', 'array', 'min:1', 'max:5'],
            'samples.*' => ['file', 'mimes:mp3,wav,m4a,mpeg,ogg', 'max:10240'],
        ];
    }
}
