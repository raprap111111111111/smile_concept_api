<?php


namespace App\Domain\Branch\Services;

use Illuminate\Support\Str;

class BranchService
{
    public function generateBranchCode(?string $providedCode, string $name): string
    {
        if ($providedCode) {
            return Str::upper($providedCode);
        }

        // Logic: 'SC-' + first 4 chars of name (stripped of spaces)
        $cleanName = str_replace(' ', '', $name);
        return 'SC-' . Str::upper(substr($cleanName, 0, 4));
    }
}