<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class InviteStaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'event_id'   => ['required', 'uuid', 'exists:events,id'],
            'staff_ids'  => ['required', 'array', 'min:1'],
            'staff_ids.*' => ['required', 'uuid', 'exists:staffs,id'],
        ];
    }
}
