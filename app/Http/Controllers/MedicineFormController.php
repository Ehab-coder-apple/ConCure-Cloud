<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\MedicineForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Manage clinic-scoped custom medicine forms.
 *
 * The built-in forms in Medicine::FORMS are immutable; this controller only
 * lists / renames / deletes the rows in the medicine_forms table that each
 * clinic adds itself (e.g. "Gel", "Lozenge", "Mouthwash").
 *
 * Access is restricted to the same roles that may edit the medicine catalog.
 */
class MedicineFormController extends Controller
{
    /**
     * Roles allowed to manage clinic forms; mirrors MedicinePolicy::update.
     */
    private const ALLOWED_ROLES = ['super_admin', 'admin', 'pharmacist'];

    /**
     * List the clinic's custom forms with the number of medicines using each.
     */
    public function index()
    {
        $user = Auth::user();
        $this->authorizeRole($user);

        if (!$user->clinic_id) {
            abort(403, __('You must be assigned to a clinic.'));
        }

        $forms = MedicineForm::byClinic($user->clinic_id)
            ->orderBy('label')
            ->get();

        $usage = Medicine::where('clinic_id', $user->clinic_id)
            ->selectRaw('form, COUNT(*) as total')
            ->groupBy('form')
            ->pluck('total', 'form')
            ->toArray();

        return view('medicines.forms.index', compact('forms', 'usage'));
    }

    /**
     * Rename a custom form. The slug (key) is intentionally NOT regenerated:
     * existing medicines reference forms by slug, so changing the slug would
     * orphan their values. Only the human-readable label is editable.
     */
    public function update(Request $request, MedicineForm $medicineForm)
    {
        $user = Auth::user();
        $this->authorizeRole($user);
        $this->authorizeOwnership($user, $medicineForm);

        $request->validate([
            'label' => 'required|string|max:80',
        ]);

        $newLabel = trim($request->input('label'));

        // Don't allow a label that collides (case-insensitive) with a built-in
        // canonical label - the dropdown would render two entries with the
        // same visible text but different underlying keys.
        $lower = strtolower($newLabel);
        foreach (Medicine::FORMS as $builtIn) {
            if (strtolower($builtIn) === $lower) {
                return back()->with('error', __('That name is already used by a built-in form.'));
            }
        }

        $medicineForm->update(['label' => $newLabel]);

        return redirect()
            ->route('medicines.forms.index')
            ->with('success', __('Form renamed successfully.'));
    }

    /**
     * Delete a custom form. Blocked when any medicine in the clinic still
     * references it, to prevent leaving stale slugs in the catalog.
     */
    public function destroy(MedicineForm $medicineForm)
    {
        $user = Auth::user();
        $this->authorizeRole($user);
        $this->authorizeOwnership($user, $medicineForm);

        $inUse = Medicine::where('clinic_id', $medicineForm->clinic_id)
            ->where('form', $medicineForm->key)
            ->count();

        if ($inUse > 0) {
            return back()->with(
                'error',
                __(':count medicine(s) still use this form. Reassign them before deleting.', ['count' => $inUse])
            );
        }

        $medicineForm->delete();

        return redirect()
            ->route('medicines.forms.index')
            ->with('success', __('Form deleted successfully.'));
    }

    private function authorizeRole($user): void
    {
        if (!$user || !in_array($user->role, self::ALLOWED_ROLES, true)) {
            abort(403);
        }
    }

    private function authorizeOwnership($user, MedicineForm $form): void
    {
        if ($form->clinic_id !== $user->clinic_id) {
            abort(404);
        }
    }
}
