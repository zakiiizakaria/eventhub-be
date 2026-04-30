<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ScanAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event_id'         => ['required', 'uuid', 'exists:events,id'],
            'invitation_token' => ['required', 'uuid'],
        ];
    }
}
