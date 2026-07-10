<?php

namespace App\Domain\Consents\Repositories;

use App\Models\PatientConsent;
use App\Support\Query\BaseRepository;

class PatientConsentRepository extends BaseRepository
{
    protected string $model = PatientConsent::class;
    protected array $searchable = [];
    protected array $filterable = ['consent_template_id', 'user_id', 'appointment_id'];
    protected array $sortable = ['id', 'signed_at'];
    protected string $defaultOrderBy = 'signed_at';
    protected string $defaultOrderDirection = 'desc';
}
