<?php

namespace Database\Seeders;

use App\Models\NotificationTemplate;
use Illuminate\Database\Seeder;

class NotificationTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            [
                'key' => 'appointment_confirmation',
                'name' => 'Appointment Confirmation',
                'subject' => 'Appointment Confirmed',
                'body' => "Hello {{ patient_name }},\n\nYour appointment is confirmed for {{ appointment_date }} at {{ appointment_time }}.\n\nDoctor: {{ doctor_name }}\nBranch: {{ branch_name }}\n\nThank you,\nSmile Concept Dental",
                'channels' => ['mail', 'database'],
                'variables' => ['patient_name', 'appointment_date', 'appointment_time', 'doctor_name', 'branch_name'],
                'trigger_event' => 'appointment.created',
                'is_active' => true,
            ],
            [
                'key' => 'appointment_reminder',
                'name' => 'Appointment Reminder',
                'subject' => 'Appointment Reminder',
                'body' => "Hi {{ patient_name }},\n\nThis is a reminder for your appointment on {{ appointment_date }} at {{ appointment_time }}.\n\nPlease arrive 10 minutes early.\n\nSmile Concept Dental",
                'channels' => ['mail', 'database'],
                'variables' => ['patient_name', 'appointment_date', 'appointment_time'],
                'trigger_event' => 'appointment.reminder',
                'is_active' => true,
            ],
            [
                'key' => 'appointment_cancelled',
                'name' => 'Appointment Cancelled',
                'subject' => 'Appointment Cancelled',
                'body' => "Hello {{ patient_name }},\n\nYour appointment scheduled for {{ appointment_date }} at {{ appointment_time }} has been cancelled.\n\nReason: {{ reason }}\n\nYou may book a new appointment anytime.\n\nSmile Concept Dental",
                'channels' => ['mail', 'database'],
                'variables' => ['patient_name', 'appointment_date', 'appointment_time', 'reason'],
                'trigger_event' => 'appointment.cancelled',
                'is_active' => true,
            ],
            [
                'key' => 'appointment_rescheduled',
                'name' => 'Appointment Rescheduled',
                'subject' => 'Appointment Rescheduled',
                'body' => "Hello {{ patient_name }},\n\nYour appointment has been rescheduled to {{ appointment_date }} at {{ appointment_time }}.\n\nDoctor: {{ doctor_name }}\nBranch: {{ branch_name }}\n\nSmile Concept Dental",
                'channels' => ['mail', 'database'],
                'variables' => ['patient_name', 'appointment_date', 'appointment_time', 'doctor_name', 'branch_name'],
                'trigger_event' => 'appointment.rescheduled',
                'is_active' => true,
            ],
            [
                'key' => 'invoice_due',
                'name' => 'Invoice Due',
                'subject' => 'Invoice Due Reminder',
                'body' => "Hello {{ patient_name }},\n\nInvoice {{ invoice_number }} has an outstanding balance of ₱{{ balance_due }}.\n\nDue Date: {{ due_date }}\n\nPlease settle your balance at your earliest convenience.\n\nSmile Concept Dental",
                'channels' => ['mail', 'database'],
                'variables' => ['patient_name', 'invoice_number', 'balance_due', 'due_date'],
                'trigger_event' => 'invoice.due',
                'is_active' => true,
            ],
            [
                'key' => 'payment_received',
                'name' => 'Payment Received',
                'subject' => 'Payment Received',
                'body' => "Thank you, {{ patient_name }}!\n\nWe received your payment for invoice {{ invoice_number }}.\n\nAmount Paid: ₱{{ amount_paid }}\n\nSmile Concept Dental",
                'channels' => ['mail', 'database'],
                'variables' => ['patient_name', 'invoice_number', 'amount_paid'],
                'trigger_event' => 'payment.received',
                'is_active' => true,
            ],
            [
                'key' => 'recall_notice',
                'name' => 'Recall Notice',
                'subject' => 'Time for Your Dental Recall Visit',
                'body' => "Hello {{ patient_name }},\n\nIt is time for your dental recall visit.\n\nRecall Type: {{ recall_type }}\nDue Date: {{ due_date }}\n\nPlease contact us to schedule your appointment.\n\nSmile Concept Dental",
                'channels' => ['mail', 'database'],
                'variables' => ['patient_name', 'recall_type', 'due_date'],
                'trigger_event' => 'recall.due',
                'is_active' => true,
            ],
            [
                'key' => 'prescription_ready',
                'name' => 'Prescription Ready',
                'subject' => 'Your Prescription Is Ready',
                'body' => "Hello {{ patient_name }},\n\nYour prescription from {{ doctor_name }} is ready.\n\nPrescription Number: {{ prescription_number }}\n\nSmile Concept Dental",
                'channels' => ['mail', 'database'],
                'variables' => ['patient_name', 'doctor_name', 'prescription_number'],
                'trigger_event' => 'prescription.ready',
                'is_active' => true,
            ],
        ];

        foreach ($templates as $template) {
            NotificationTemplate::updateOrCreate(
                ['key' => $template['key']],
                $template
            );
        }
    }
}