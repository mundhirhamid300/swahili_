<?php

/** Hii request huhakikisha data ya fomu ni sahihi kabla haijatumika. */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SpeakRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'text' => ['required', 'string', 'max:1000'],
            'lang' => ['nullable', 'in:en,sw'],
        ];
    }
}
