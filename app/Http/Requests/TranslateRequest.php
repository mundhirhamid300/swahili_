<?php

/** Hii request huhakikisha data ya fomu ni sahihi kabla haijatumika. */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class TranslateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'text' => ['required', 'string', 'max:1000'],
            'from' => ['required', 'in:en,sw'],
            'to' => ['required', 'in:en,sw'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->input('from') === $this->input('to')) {
                $validator->errors()->add('to', 'Source and target languages must differ.');
            }
        });
    }
}
