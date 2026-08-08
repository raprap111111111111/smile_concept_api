<?php

namespace App\Domain\PatientAttachments\Repositories;

use App\Models\PatientAttachment;
use App\Support\Query\BaseRepository;

class PatientAttachmentRepository extends BaseRepository
{
    protected string $model = PatientAttachment::class;

    protected array $searchable = ['file_name', 'notes'];

    protected array $filterable = [
        'user_id',
        'appointment_id',
        'file_type',
        // `category`, `is_xray` and `scan_status` are NOT columns on
        // patient_attachments — the migration adding them never landed. They
        // were harmless while filters silently no-opped, but now that filters
        // are honoured they would raise "Unknown column". Re-add each one
        // together with its migration; GetAllPatientAttachmentsRequest still
        // validates them, so only the storage is missing.
    ];

    protected array $sortable = [
        'id',
        'created_at',
        // `scanned_at` is likewise not a column yet.
    ];

    protected string $defaultOrderBy = 'created_at';
    protected string $defaultOrderDirection = 'desc';

    // 🔴 ADD THIS to verify
    public function debugInfo(): array
    {
        return [
            'model'      => $this->model,
            'filterable' => $this->filterable,
            'searchable' => $this->searchable,
            'sortable'   => $this->sortable,
        ];
    }
}