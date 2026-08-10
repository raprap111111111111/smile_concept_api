<?php

namespace App\Policies;

use App\Models\PatientConsent;
use App\Models\User;

class PatientConsentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('consent-form.viewAny')
            || $user->can('consent-form.viewOwn');
    }

    public function view(User $user, PatientConsent $consent): bool
    {
        if ($user->can('consent-form.viewAny')) {
            return true;
        }

        return $user->can('consent-form.viewOwn')
            && $this->owns($user, $consent);
    }

    public function sign(User $user): bool
    {
        // Staff signs on behalf of patient in-clinic
        return $user->can('consent-form.sign');
    }

    public function print(User $user, PatientConsent $consent): bool
    {
        if ($user->can('consent-form.print')) {
            return true;
        }

        return $user->can('consent-form.viewOwn')
            && $this->owns($user, $consent);
    }

    public function void(User $user, PatientConsent $consent): bool
    {
        return $user->can('consent-form.void') && ! $consent->isVoided();
    }

    public function delete(User $user, PatientConsent $consent): bool
    {
        return $user->can('consent-form.delete');
    }

    private function owns(User $user, PatientConsent $consent): bool
    {
        return (int) $consent->user_id === (int) $user->id;
    }
}