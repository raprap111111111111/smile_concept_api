<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * ═══════════════════════════════════════════════════════
 * SERVES FILES TRAIT
 * ═══════════════════════════════════════════════════════
 *
 * Reusable trait for streaming files from Laravel storage
 * through authenticated endpoints.
 *
 * Uses the 'public' disk by default (works with storage:link).
 * Deploy-safe: paths resolve from config, not hardcoded.
 */
trait ServesFiles
{
    /**
     * Stream a file inline (view in browser).
     */
    protected function streamFile(
        ?string $relativePath,
        string $disk = 'public',
        ?string $downloadName = null,
        bool $forceDownload = false,
        int $cacheSeconds = 3600,
    ): BinaryFileResponse {

        abort_if(empty($relativePath), 404, 'File path not provided.');

        $storage = Storage::disk($disk);

        abort_unless($storage->exists($relativePath), 404, 'File not found.');

        $fileName    = $downloadName ?? basename($relativePath);
        $mimeType    = $this->resolveMimeType($disk, $relativePath);
        $disposition = $forceDownload ? 'attachment' : 'inline';

        return response()->file(
            $storage->path($relativePath),
            [
                'Content-Type'           => $mimeType,
                'Content-Disposition'    => sprintf('%s; filename="%s"', $disposition, addslashes($fileName)),
                'Cache-Control'          => sprintf('private, max-age=%d', $cacheSeconds),
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }

    /**
     * Force file download.
     */
    protected function downloadFile(
        ?string $relativePath,
        string $disk = 'public',
        ?string $downloadName = null,
    ): BinaryFileResponse {
        return $this->streamFile(
            relativePath: $relativePath,
            disk: $disk,
            downloadName: $downloadName,
            forceDownload: true,
            cacheSeconds: 0,
        );
    }

    /**
     * Resolve mime type safely.
     */
    private function resolveMimeType(string $disk, string $path): string
    {
        try {
            return Storage::disk($disk)->mimeType($path) ?: 'application/octet-stream';
        } catch (\Throwable $e) {
            return 'application/octet-stream';
        }
    }
}