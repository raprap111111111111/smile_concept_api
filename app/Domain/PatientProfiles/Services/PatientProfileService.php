<?php

namespace App\Domain\PatientProfiles\Services;

class PatientProfileService
{
    /**
     * Validates contact details format structure
     */
    public function validateContactPhone(string $phone): void
    {
        $clean = preg_replace('/[^0-9+]/', '', $phone);
        
        if (strlen($clean) < 7 || strlen($clean) > 15) {
            throw new \InvalidArgumentException("Emergency contact phone number format is invalid.");
        }
    }
}
