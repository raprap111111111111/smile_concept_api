<?php

namespace App\Domain\Prescriptions\Mappers;

use App\Domain\Prescriptions\DTOs\CreatePrescriptionDTO;
use App\Domain\Prescriptions\DTOs\CreatePrescriptionItemDTO;
use App\Domain\Prescriptions\DTOs\UpdatePrescriptionDTO;
use App\Http\Requests\v1\Prescription\StorePrescriptionRequest;
use App\Http\Requests\v1\Prescription\UpdatePrescriptionRequest;

class PrescriptionMapper
{
    public static function fromCreateRequest(StorePrescriptionRequest $request): CreatePrescriptionDTO
    {
        $items = array_map(function ($item) {
            return new CreatePrescriptionItemDTO(
                medicineName: $item['medicine_name'],
                dosage: $item['dosage'],
                frequency: $item['frequency'],
                durationDays: (int) $item['duration_days'],
                instructions: $item['instructions'] ?? null
            );
        }, $request->validated('items', []));

        return new CreatePrescriptionDTO(
            appointmentId: $request->validated('appointment_id') ? (int) $request->validated('appointment_id') : null,
            doctorId: (int) $request->validated('doctor_id'),
            userId: (int) $request->validated('user_id'),
            notes: $request->validated('notes'),
            items: $items
        );
    }

    public static function fromUpdateRequest(UpdatePrescriptionRequest $request): UpdatePrescriptionDTO
    {
        $items = null;
        if ($request->has('items')) {
            $items = array_map(function ($item) {
                return new CreatePrescriptionItemDTO(
                    medicineName: $item['medicine_name'],
                    dosage: $item['dosage'],
                    frequency: $item['frequency'],
                    durationDays: (int) $item['duration_days'],
                    instructions: $item['instructions'] ?? null
                );
            }, $request->validated('items', []));
        }

        return new UpdatePrescriptionDTO(
            appointmentId: $request->validated('appointment_id') ? (int) $request->validated('appointment_id') : null,
            doctorId: $request->validated('doctor_id') ? (int) $request->validated('doctor_id') : null,
            userId: $request->validated('user_id') ? (int) $request->validated('user_id') : null,
            notes: $request->validated('notes'),
            items: $items
        );
    }
}
