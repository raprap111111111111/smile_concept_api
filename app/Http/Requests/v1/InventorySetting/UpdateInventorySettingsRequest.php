<?php

namespace App\Http\Requests\v1\InventorySetting;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Every key is `required`, so a save is all-or-nothing — the same contract as
 * UpdateAppointmentSettingsRequest. A partial save would let the form write
 * back a half-populated view of settings it had not finished loading.
 */
class UpdateInventorySettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('setting.update');
    }

    public function rules(): array
    {
        return [
            'inventory_auto_deduct_enabled'  => ['required', 'boolean'],
            'inventory_allow_negative_stock' => ['required', 'boolean'],
            'inventory_track_expiry'         => ['required', 'boolean'],

            'inventory_expiry_warning_days'       => ['required', 'integer', 'min:1', 'max:365'],
            'inventory_default_minimum_threshold' => ['required', 'integer', 'min:0', 'max:100000'],

            'inventory_low_stock_alert_enabled' => ['required', 'boolean'],
            // Clock hour. Out of range would mean the digest silently never fires.
            'inventory_low_stock_alert_hour'    => ['required', 'integer', 'min:0', 'max:23'],
            'inventory_low_stock_cooldown_days' => ['required', 'integer', 'min:0', 'max:90'],
        ];
    }

    public function messages(): array
    {
        return [
            'inventory_low_stock_alert_hour.min' => 'Pick an hour between 0 and 23.',
            'inventory_low_stock_alert_hour.max' => 'Pick an hour between 0 and 23.',
            'inventory_expiry_warning_days.min'  => 'The expiry warning window must be at least one day.',
        ];
    }
}
