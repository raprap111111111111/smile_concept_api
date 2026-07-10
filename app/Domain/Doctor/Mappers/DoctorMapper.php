<?php
// app/Domain/Doctor/Mappers/DoctorMapper.php

namespace App\Domain\Doctor\Mappers;

use App\Domain\Doctor\DTOs\CreateDoctorDTO;
use App\Domain\Doctor\DTOs\UpdateDoctorDTO;
use App\Http\Requests\v1\Doctor\StoreDoctorRequest;
use App\Http\Requests\v1\Doctor\UpdateDoctorRequest;

class DoctorMapper
{
    public static function fromCreateRequest(StoreDoctorRequest $request): CreateDoctorDTO
    {
        return new CreateDoctorDTO(
            userId:            (int) $request->validated('user_id'),
            licenseNumber:     $request->validated('license_number'),
            specialization:    $request->validated('specialization'),
            bio:               $request->validated('bio'),
            consultationFee:   $request->validated('consultation_fee') !== null
                                    ? (float) $request->validated('consultation_fee')
                                    : null,
            yearsOfExperience: (int) ($request->validated('years_of_experience') ?? 0),
            signaturePath:     $request->validated('signature_path'),
            isActive:          $request->boolean('is_active', true),
        );
    }

    public static function fromUpdateRequest(UpdateDoctorRequest $request): UpdateDoctorDTO
    {
        return new UpdateDoctorDTO(
            licenseNumber:     $request->validated('license_number'),
            specialization:    $request->validated('specialization'),
            bio:               $request->validated('bio'),
            consultationFee:   $request->has('consultation_fee') && $request->validated('consultation_fee') !== null
                                    ? (float) $request->validated('consultation_fee')
                                    : null,
            yearsOfExperience: $request->has('years_of_experience') && $request->validated('years_of_experience') !== null
                                    ? (int) $request->validated('years_of_experience')
                                    : null,
            signaturePath:     $request->validated('signature_path'),
            isActive:          $request->has('is_active') ? $request->boolean('is_active') : null,
        );
    }
}