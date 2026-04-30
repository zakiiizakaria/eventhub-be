<?php

declare(strict_types=1);

namespace App\Http\Requests\Public;

use Illuminate\Foundation\Http\FormRequest;

class RSVPSubmitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'is_attending' => ['required', 'boolean'],
            'pax'          => ['required_if:is_attending,true', 'integer', 'min:1', 'max:10'],
        ];
    }
}
