<?php

namespace App\Http\Requests;

use App\Enums\DeviceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateDeviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'          => 'sometimes|required|string|max:255',
            'serial_number' => 'sometimes|required|string|unique:devices,serial_number,' . $this->route('device')->id . '|max:100',
            'type'          => ['required', new Enum(DeviceType::class)],
        ];
    }
}
