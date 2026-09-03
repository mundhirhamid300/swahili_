<?php

/** Hii request huhakikisha data ya fomu ni sahihi kabla haijatumika. */

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AiAnalyzeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'text' => ['required', 'string', 'max:1000'],
            'mode' => ['required', 'in:grammar,vocabulary,sentence'],
            'from' => ['nullable', 'in:en,sw'],
            'to' => ['nullable', 'in:en,sw'],
        ];
    }
}
