<?php

namespace App\Domain\AppointmentTreatments\Mappers;

use App\Domain\AppointmentTreatments\DTOs\CreateAppointmentTreatmentDTO;
use App\Domain\AppointmentTreatments\DTOs\UpdateAppointmentTreatmentDTO;
use App\Http\Requests\v1\AppointmentTreatment\StoreAppointmentTreatmentRequest;
use App\Http\Requests\v1\AppointmentTreatment\UpdateAppointmentTreatmentRequest;

class AppointmentTreatmentMapper
{
    public static function fromCreateRequest(StoreAppointmentTreatmentRequest $request): CreateAppointmentTreatmentDTO
    {
        return new CreateAppointmentTreatmentDTO(
            appointmentId: (int) $request->validated('appointment_id'),
            treatmentId:   (int) $request->validated('treatment_id'),
            toothNumber:         $request->validated('tooth_number'),
            priceCharged:        $request->validated('price_charged') !== null
                                    ? (float) $request->validated('price_charged')
                                    : null,
            notes:               $request->validated('notes'),
        );
    }

    public static function fromUpdateRequest(UpdateAppointmentTreatmentRequest $request): UpdateAppointmentTreatmentDTO
    {
        return new UpdateAppointmentTreatmentDTO(
            appointmentId: $request->has('appointment_id') ? (int) $request->validated('appointment_id') : null,
            treatmentId:   $request->has('treatment_id')   ? (int) $request->validated('treatment_id')   : null,
            toothNumber:   $request->validated('tooth_number'),
            priceCharged:  $request->has('price_charged')  ? (float) $request->validated('price_charged') : null,
            notes:         $request->validated('notes'),
        );
    }
}