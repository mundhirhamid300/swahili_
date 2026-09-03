<?php

/** Hii request huhakikisha data ya fomu ni sahihi kabla haijatumika. */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFlashcardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'swahili_word' => ['required', 'string', 'max:255'],
            'english_meaning' => ['required', 'string', 'max:255'],
            'pronunciation' => ['nullable', 'string', 'max:255'],
            'audio' => [$this->isMethod('post') ? 'required' : 'nullable', 'file', 'mimes:mp3,wav,ogg,webm,m4a', 'max:5120'],
        ];
    }
}
