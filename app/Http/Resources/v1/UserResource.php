<?php

namespace App\Http\Resources\v1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $role = $this->getRoleNames()->first() ?? 'patient';
        $profile = $this->patientProfile;

        $needsSetup = ($role === 'patient') && (!$profile || is_null($profile->emergency_contact_name));

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'profile_photo_url' => $this->profile_photo_url,
            'role' => $role,                                    // ✅ primary role name (string)
            'roles' => $this->getRoleNames()->toArray(),         // ✅ all role names (array of strings)
            'permissions' => $this->getAllPermissions()          // ✅ all permission names
                ->pluck('name')
                ->toArray(),
            'needs_setup' => $needsSetup,

            'patient_profile' => $profile ? [
                'id' => $profile->id,
                'user_id' => $profile->user_id,
                'allergies' => $profile->allergies,
                'medical_history' => $profile->medical_history,
                'blood_type' => $profile->blood_type,
                'emergency_contact_name' => $profile->emergency_contact_name,
                'emergency_contact_phone' => $profile->emergency_contact_phone,
                'requires_epinephrine_free_anesthesia' => $profile->requires_epinephrine_free_anesthesia,
                'has_cardiac_conditions' => $profile->has_cardiac_conditions,
                'is_pregnant' => $profile->is_pregnant,
                'has_bleeding_disorders' => $profile->has_bleeding_disorders,
                'deleted_at' => $profile->deleted_at,
                'created_at' => $profile->created_at,
                'updated_at' => $profile->updated_at,
            ] : null,

            'branches' => $this->branches->map(function ($branch) {
                return [
                    'id' => $branch->id,
                    'name' => $branch->name,
                ];
            }),

            'is_active' => (bool) ($this->is_active ?? true),
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}