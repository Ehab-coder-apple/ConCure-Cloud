<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait AppliesAccessibleClinicScope
{
    protected static function bootAppliesAccessibleClinicScope(): void
    {
        static::addGlobalScope('accessible_clinics', function (Builder $query) {
            if (!auth()->check()) {
                return;
            }

            $user = auth()->user();
            if (!$user instanceof User || $user->hasGlobalClinicAccess()) {
                return;
            }

            $clinicIds = $user->accessibleClinicIds();
            if ($clinicIds === []) {
                $query->whereRaw('1 = 0');
                return;
            }

            $query->whereIn($query->qualifyColumn('clinic_id'), $clinicIds);
        });
    }
}