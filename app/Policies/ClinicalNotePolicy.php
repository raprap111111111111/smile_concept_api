<?php

namespace App\Policies;

use App\Models\ClinicalNote;
use App\Models\User;

class ClinicalNotePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('clinical-note.viewAny');
    }

    public function view(User $user, ClinicalNote $note): bool
    {
        return $user->can('clinical-note.view');
    }

    public function create(User $user): bool
    {
        return $user->can('clinical-note.create');
    }

    public function update(User $user, ClinicalNote $note): bool
    {
        return $user->can('clinical-note.update');
    }

    public function delete(User $user, ClinicalNote $note): bool
    {
        return $user->can('clinical-note.delete');
    }

    public function finalize(User $user, ClinicalNote $note): bool
    {
        return $user->can('clinical-note.finalize');
    }

    public function amend(User $user, ClinicalNote $note): bool
    {
        return $user->can('clinical-note.amend');
    }
}