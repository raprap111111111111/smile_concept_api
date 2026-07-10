<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            // ─── Business ─────────────────────────────
            ['key' => 'clinic_name',   'value' => 'Smile Concept Dental', 'group' => 'business', 'type' => 'string',  'label' => 'Clinic Name',   'is_public' => true],
            ['key' => 'clinic_phone',  'value' => '+63 917 123 4567',     'group' => 'business', 'type' => 'string',  'label' => 'Clinic Phone',  'is_public' => true],
            ['key' => 'clinic_email',  'value' => 'info@smileconcept.ph', 'group' => 'business', 'type' => 'string',  'label' => 'Clinic Email',  'is_public' => true],
            ['key' => 'clinic_address','value' => '123 Ayala Ave, Makati','group' => 'business', 'type' => 'string',  'label' => 'Address',       'is_public' => true],

            // ─── Booking ──────────────────────────────
            ['key' => 'booking_lead_time_hours',   'value' => '2',  'group' => 'booking', 'type' => 'integer', 'label' => 'Booking Lead Time (hours)'],
            ['key' => 'appointment_slot_duration', 'value' => '30', 'group' => 'booking', 'type' => 'integer', 'label' => 'Slot Duration (minutes)'],
            ['key' => 'cancellation_window_hours', 'value' => '24', 'group' => 'booking', 'type' => 'integer', 'label' => 'Cancellation Window'],
            ['key' => 'allow_online_booking',      'value' => '1',  'group' => 'booking', 'type' => 'boolean', 'label' => 'Allow Online Booking'],

            // ─── Billing ──────────────────────────────
            ['key' => 'tax_rate',       'value' => '12.00', 'group' => 'billing', 'type' => 'float',  'label' => 'Tax Rate (%)'],
            ['key' => 'currency',       'value' => 'PHP',   'group' => 'billing', 'type' => 'string', 'label' => 'Currency', 'is_public' => true],
            ['key' => 'invoice_prefix', 'value' => 'INV',   'group' => 'billing', 'type' => 'string', 'label' => 'Invoice Prefix', 'is_editable' => false],

            // ─── Reminders ────────────────────────────
            ['key' => 'reminder_offsets',  'value' => '[24,1]',           'group' => 'reminders', 'type' => 'json',    'label' => 'Reminder Offsets (hours)'],
            ['key' => 'reminder_channels', 'value' => '["email","sms"]',  'group' => 'reminders', 'type' => 'json',    'label' => 'Reminder Channels'],
            ['key' => 'sms_enabled',       'value' => '1',                'group' => 'reminders', 'type' => 'boolean', 'label' => 'SMS Enabled'],
            ['key' => 'email_enabled',     'value' => '1',                'group' => 'reminders', 'type' => 'boolean', 'label' => 'Email Enabled'],

            // ─── Branding (frontend theme) ────────────
            ['key' => 'primary_color',   'value' => '#2563eb', 'group' => 'branding', 'type' => 'string', 'label' => 'Primary Color',   'is_public' => true],
            ['key' => 'secondary_color', 'value' => '#f59e0b', 'group' => 'branding', 'type' => 'string', 'label' => 'Secondary Color', 'is_public' => true],
            ['key' => 'logo_url',        'value' => '/logo.png','group'=> 'branding', 'type' => 'string', 'label' => 'Logo URL',        'is_public' => true],

            // ─── Feature Flags ────────────────────────
            ['key' => 'enable_dental_chart',   'value' => '1', 'group' => 'features', 'type' => 'boolean', 'label' => 'Enable Dental Chart'],
            ['key' => 'enable_patient_portal', 'value' => '1', 'group' => 'features', 'type' => 'boolean', 'label' => 'Enable Patient Portal'],
            ['key' => 'maintenance_mode',      'value' => '0', 'group' => 'features', 'type' => 'boolean', 'label' => 'Maintenance Mode', 'is_public' => true],
        ];

        foreach ($defaults as $data) {
            Setting::updateOrCreate(
                ['key' => $data['key']],
                array_merge([
                    'is_public'   => false,
                    'is_editable' => true,
                ], $data)
            );
        }
    }
}