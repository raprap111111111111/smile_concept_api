<?php

namespace App\Policies;

use App\Models\PatientAttachment;
use App\Models\User;

class PatientAttachmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('attachment.viewAny');
    }

    public function view(User $user, PatientAttachment $attachment): bool
    {
        return $user->can('attachment.view');
    }

    public function create(User $user): bool
    {
        return $user->can('attachment.create');
    }

    public function update(User $user, PatientAttachment $attachment): bool
    {
        return $user->can('attachment.update');
    }

    public function delete(User $user, PatientAttachment $attachment): bool
    {
        return $user->can('attachment.delete');
    }

    public function download(User $user, PatientAttachment $attachment): bool
    {
        return $user->can('attachment.download');
    }

    public function upload(User $user): bool
    {
        return $user->can('attachment.upload');
    }
}