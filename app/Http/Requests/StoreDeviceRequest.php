<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeviceRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $this->merge([
            'has_recording_func' => $this->has_recording_func === 'on',
            'has_livestream_func' => $this->has_livestream_func === 'on',
            'is_hybrid' => $this->is_hybrid === 'on',
            'operational' => $this->operational === 'on',

        ]);
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return auth()->user()->isAdmin() || auth()->user()->isAssistant();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'location_id' => ['exists:App\Models\DeviceLocation,id'],
            'organization_id' => ['exists:App\Models\Organization,org_id'],
            'opencast_device_name' => ['string'],
            'url' => ['url', 'nullable'],
            'room_url' => ['url', 'nullable'],
            'camera_url' => ['url', 'nullable'],
            'power_outlet_url' => ['url', 'nullable'],
            'ip_address' => ['ipv4', 'nullable'],
            'has_recording_func' => ['boolean'],
            'has_livestream_func' => ['boolean'],
            'is_hybrid' => ['boolean'],
            'operational' => ['boolean'],
            'description' => ['string', 'nullable'],
            'comment' => ['string', 'nullable'],
            'telephone_number' => ['digits_between:5,12', 'nullable'],
        ];
    }
}
