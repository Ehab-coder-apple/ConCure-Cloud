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
        return $user->hasAnyRole(['admin', 'doctor', 'nurse', 'assistant', 'pharmacist']);
    }

    /**
     * Determine whether the user can view the medicine.
     */
    public function view(User $user, Medicine $medicine): bool
    {
        // Must be in the same clinic
        if ($user->clinic_id !== $medicine->clinic_id) {
            return false;
        }

        // Admins and pharmacists can view all medicines in their clinic
        if ($user->isSuperAdmin() || $user->isClinicAdmin() || $user->role === 'pharmacist') {
            return true;
        }

        // Regular users can view their own medicines or admin-uploaded medicines
        if ($medicine->created_by === $user->id) {
            return true;
        }

        // Check if medicine was uploaded by an admin
        $creator = $medicine->creator;
        if ($creator && ($creator->role === 'super_admin' || $creator->role === 'admin')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create medicines.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'doctor', 'pharmacist']);
    }

    /**
     * Determine whether the user can update the medicine.
     */
    public function update(User $user, Medicine $medicine): bool
    {
        // Must be in the same clinic
        if ($user->clinic_id !== $medicine->clinic_id) {
            return false;
        }

        // Admins and pharmacists can update all medicines in their clinic
        if ($user->isSuperAdmin() || $user->isClinicAdmin() || $user->role === 'pharmacist') {
            return true;
        }

        // Regular users can only update their own medicines
        return $medicine->created_by === $user->id && $user->hasAnyRole(['doctor']);
    }

    /**
     * Determine whether the user can delete the medicine.
     */
    public function delete(User $user, Medicine $medicine): bool
    {
        // Must be in the same clinic
        if ($user->clinic_id !== $medicine->clinic_id) {
            return false;
        }

        // Admins and pharmacists can delete all medicines in their clinic
        if ($user->isSuperAdmin() || $user->isClinicAdmin() || $user->role === 'pharmacist') {
            return true;
        }

        // Regular users can only delete their own medicines
        return $medicine->created_by === $user->id && $user->hasAnyRole(['doctor']);
    }
}
