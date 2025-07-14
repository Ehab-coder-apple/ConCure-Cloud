<?php

namespace App\Policies;

use App\Models\Medicine;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MedicinePolicy
{
    /**
     * Determine whether the user can view any medicines.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'doctor', 'nurse', 'assistant']);
    }

    /**
     * Determine whether the user can view the medicine.
     */
    public function view(User $user, Medicine $medicine): bool
    {
        return $user->clinic_id === $medicine->clinic_id;
    }

    /**
     * Determine whether the user can create medicines.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'doctor']);
    }

    /**
     * Determine whether the user can update the medicine.
     */
    public function update(User $user, Medicine $medicine): bool
    {
        return $user->clinic_id === $medicine->clinic_id &&
               $user->hasAnyRole(['admin', 'doctor']);
    }

    /**
     * Determine whether the user can delete the medicine.
     */
    public function delete(User $user, Medicine $medicine): bool
    {
        return $user->clinic_id === $medicine->clinic_id &&
               $user->hasAnyRole(['admin', 'doctor']);
    }
}
