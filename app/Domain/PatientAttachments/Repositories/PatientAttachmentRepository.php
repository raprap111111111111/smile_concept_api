<?php
namespace App\Domain\PatientAttachments\Repositories;

use App\Models\PatientAttachment;
use App\Support\Query\BaseRepository;

class PatientAttachmentRepository extends BaseRepository
{
    protected string $model = PatientAttachment::class;
    protected array $searchable  = ['file_name', 'notes'];
    protected array $filterable  = [
        'user_id',
        'appointment_id',
        'file_type',
        'category',     
        'is_xray',       
        'scan_status',   
    ];
    protected array $sortable    = ['id', 'created_at', 'scanned_at'];
    protected string $defaultOrderBy        = 'created_at';
    protected string $defaultOrderDirection = 'desc';
}