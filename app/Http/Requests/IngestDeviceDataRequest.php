<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IngestDeviceDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'       => 'required|string|in:on,off',
            'start_uptime' => 'nullable|date',
            'end_uptime'   => 'nullable|date',
            'logged_at'    => 'nullable|date',
        ];
    }
}
