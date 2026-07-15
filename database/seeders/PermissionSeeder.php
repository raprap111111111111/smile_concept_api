<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 🧹 Clear cached permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $crud = ['viewAny', 'view', 'create', 'update', 'delete', 'restore', 'forceDelete'];

        $entities = [

            // ═══════════════════════════════════════════
            // 📊 PHASE 0: DASHBOARD & SETTINGS
            // ═══════════════════════════════════════════
            'dashboard'        => ['view'],
            'setting'          => ['view', 'update'],
            'activity-log'     => ['viewAny', 'view', 'export'],   // ✅ merged audit-log here
            'notification'     => ['viewAny', 'view', 'create', 'update', 'delete'], // ✅ added create

            // ═══════════════════════════════════════════
            // 🔐 PHASE 1: SECURITY & IDENTITY
            // ═══════════════════════════════════════════
            'user'             => array_merge($crud, ['impersonate', 'reset-password']),
            'role'             => $crud,
            'permission'       => $crud,

            // ═══════════════════════════════════════════
            // 🏢 BRANCH & ORGANIZATION
            // ═══════════════════════════════════════════
            'branch'           => $crud,

            // ═══════════════════════════════════════════
            // 🩺 DOCTOR & SCHEDULING
            // ═══════════════════════════════════════════
            'doctor'           => $crud,
            'doctor-schedule'  => $crud,

            // ═══════════════════════════════════════════
            // 📅 PHASE 2: APPOINTMENTS & SCHEDULING
            // ═══════════════════════════════════════════
            'appointment'      => array_merge($crud, [
                'approve',
                'reject',
                'cancel',
                'reschedule',
                'check-in',
                'check-out',
                'no-show',
                'complete',
                'update-status',
                'create-for-others'
            ]),

            // 🔔 Recall System
            'recall'           => array_merge($crud, ['send-reminder', 'mark-completed']),

            // ═══════════════════════════════════════════
            // 🧑 PATIENT MANAGEMENT
            // ═══════════════════════════════════════════
            'patient'          => array_merge($crud, ['export', 'merge', 'transfer-branch']),
            'medical-profile'  => $crud,
            'medical-item'     => $crud,
            'medical-alert'    => $crud,

            // ═══════════════════════════════════════════
            // 🦷 PHASE 3: CLINICAL OPERATIONS (EHR)
            // ═══════════════════════════════════════════
            'dental-chart'     => $crud,
            'clinical-note'    => array_merge($crud, ['finalize', 'amend']),
            'treatment'        => $crud,
            // ─────────────────────────────────────────

            'treatment-plan' => array_merge($crud, [
                'send-to-patient',
                'accept',
                'reject',
                'mark-completed',
                'reopen',
                'change-status',
            ]),
            'prescription'     => array_merge($crud, ['print', 'send']),
            'attachment'       => array_merge($crud, ['download', 'upload']),
            'consent-form'     => array_merge($crud, [
                'send',
                'sign',
                'void',
                'print',
            ]),

            // ═══════════════════════════════════════════
            // 🦷 LABORATORY CASE TRACKING
            // ═══════════════════════════════════════════
            'lab'              => $crud,
            'lab-case'         => array_merge($crud, [
                'send',
                'receive',
                'quality-check',
                'install',
                'return',
            ]),

            // ═══════════════════════════════════════════
            // 💰 PHASE 4: FINANCIAL OPERATIONS
            // ═══════════════════════════════════════════
            'invoice'          => array_merge($crud, [
                'send',
                'mark-paid',
                'void',
                'print',
                'export',
                'refund',
            ]),
            'payment'          => array_merge($crud, [
                'refund',
                'void',
                'print-receipt',
                'export',
            ]),
            'discount'         => $crud,
            'tax'              => $crud,

            // ═══════════════════════════════════════════
            // 📦 INVENTORY MANAGEMENT
            // ═══════════════════════════════════════════
            'inventory'        => array_merge($crud, [
                'adjust',
                'transfer',
                'stock-in',
                'stock-out',
                'export',
            ]),
            'inventory-category' => $crud,
            'supplier'         => $crud,
            'purchase-order'   => array_merge($crud, [
                'approve',
                'receive',
                'cancel',
            ]),

            // ═══════════════════════════════════════════
            // 👔 HR & EMPLOYEE MANAGEMENT
            // ═══════════════════════════════════════════
            'employee'         => array_merge($crud, ['import', 'export']),
            'attendance'       => array_merge($crud, ['check-in', 'check-out']),
            'leave-request'    => array_merge($crud, [
                'prepare',
                'note',
                'approve',
                'receive',
                'reject',
            ]),
            'payroll'          => array_merge($crud, [
                'generate',
                'approve',
                'pay',
                'export',
            ]),

            // ═══════════════════════════════════════════
            // 🌐 LANDING PAGE CONTENT
            // ═══════════════════════════════════════════
            'service'          => $crud,
            'gallery'          => $crud,
            'testimonial'      => array_merge($crud, ['approve', 'reject']),
            'announcement'     => array_merge($crud, ['publish', 'unpublish']),
            'faq'              => $crud,

            // ═══════════════════════════════════════════
            // 📊 REPORTS & ANALYTICS
            // ═══════════════════════════════════════════
            'report'           => ['view', 'export'],
            'analytics'        => ['view'],
            'financial-report' => ['view', 'export'],
            'clinical-report'  => ['view', 'export'],
            'inventory-report' => ['view', 'export'],
            'patient-report'   => ['view', 'export'],

            // ═══════════════════════════════════════════
            // 🔔 COMMUNICATIONS
            // ═══════════════════════════════════════════
            'sms'              => ['send', 'view', 'configure'],
            'email'            => ['send', 'view', 'configure'],
            'reminder'         => array_merge($crud, ['send']),

            // ═══════════════════════════════════════════
            // 🔧 SYSTEM
            // ═══════════════════════════════════════════
            'backup'           => ['create', 'restore', 'download', 'delete'],

        ];

        $totalPermissions = 0;

        foreach ($entities as $resource => $actions) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name'       => "{$resource}.{$action}",
                    'guard_name' => 'api',
                ]);
                $totalPermissions++;
            }
        }

        $this->command->info("✅ {$totalPermissions} permissions seeded successfully!");
    }
}
