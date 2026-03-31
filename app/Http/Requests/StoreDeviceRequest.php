<?php

namespace App\Http\Requests;

use App\Enums\DeviceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreDeviceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'          => 'required|string|max:255',
            'serial_number' => 'required|string|unique:devices,serial_number|max:100',
            'type'          => ['required', new Enum(DeviceType::class)],
            'outlet_id'     => 'required|exists:outlets,id',
        ];
    }

    public function messages(): array
    {
        return [
            'serial_number.unique' => 'Serial number sudah terdaftar.',
            'type.Illuminate\Validation\Rules\Enum' => 'Tipe device tidak valid.',
        ];
    }
}
