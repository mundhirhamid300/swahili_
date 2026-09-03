<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/** Huthibitisha data inayotumwa kuunda au kusasisha kozi. */
class StoreCourseRequest extends FormRequest
{
    /** Ruhusu ombi; ruhusa za jukumu hushughulikiwa na middleware ya njia. */
    public function authorize(): bool
    {
        return true;
    }

    /** Eleza kanuni za kila taarifa ya fomu ya kozi. */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'], // Jina lazima liwe maandishi yasiyozidi herufi 255.
            'description' => ['nullable', 'string'], // Maelezo ni hiari na yakitolewa yawe maandishi.
            'level' => ['required', 'in:beginner,intermediate'], // Kiwango lazima kiwe moja ya chaguo hizi.
            'topic' => ['nullable', 'string', 'max:100'], // Mada ni hiari na ina kikomo cha herufi 100.
            'status' => ['required', 'in:draft,published'], // Hali lazima iwe rasimu au iliyochapishwa.
            'thumbnail' => ['nullable', 'image', 'max:2048'], // Picha ni hiari na ukubwa wake usizidi KB 2048.
        ];
    }
}
