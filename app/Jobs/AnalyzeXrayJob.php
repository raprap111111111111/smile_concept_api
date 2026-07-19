<?php
// app/Jobs/AnalyzeXrayJob.php
namespace App\Jobs;

use App\Models\PatientAttachment;
use App\Domain\PatientAttachments\Services\XrayAnalysisService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class AnalyzeXrayJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(
        private readonly PatientAttachment $attachment
    ) {}

    public function handle(XrayAnalysisService $service): void
    {
        $service->analyze($this->attachment);
    }
}