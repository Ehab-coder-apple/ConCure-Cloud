<?php

namespace App\Http\Controllers;

use App\Models\FormTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FormTemplateController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        if (!$user->canViewPatientForms() && !$user->canManageFormTemplates()) {
            abort(403, 'You do not have permission to view form templates.');
        }

        $query = FormTemplate::query()->forClinic($user->clinic_id)
            ->search($request->get('search'));

        if ($request->filled('status')) {
            $query->where('is_active', $request->get('status') === 'active');
        }
        if ($request->filled('category')) {
            $query->where('category', 'like', '%' . $request->get('category') . '%');
        }

        $templates = $query->orderByDesc('created_at')->paginate(15);

        return view('forms.templates.index', [
            'templates' => $templates,
        ]);
    }

    public function create()
    {
        $user = Auth::user();
        if (!$user->canManageFormTemplates()) {
            abort(403, 'You do not have permission to create form templates.');
        }

        return view('forms.templates.create', [
            'allowedExtensions' => FormTemplate::allowedExtensions(),
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user->canManageFormTemplates()) {
            abort(403, 'You do not have permission to create form templates.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category' => 'nullable|string|max:100',
            'file' => 'required|file|mimes:doc,docx,xls,xlsx|max:' . config('app.concure.max_file_size'),
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $filename = time() . '_' . $originalName;
        $dir = FormTemplate::storageDirForClinic((int)$user->clinic_id);
        $path = $file->storeAs($dir, $filename, 'public');

        FormTemplate::create([
            'name' => (string)$request->input('name'),
            'description' => $request->input('description'),
            'category' => $request->input('category'),
            'file_path' => $path,
            'original_filename' => $originalName,
            'file_type' => strtolower($file->getClientOriginalExtension()),
            'file_size' => $file->getSize(),
            'is_active' => true,
            'clinic_id' => $user->clinic_id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        return redirect()->route('forms.templates.index')
            ->with('success', __('Form template uploaded successfully.'));
    }

    public function edit(FormTemplate $formTemplate)
    {
        $user = Auth::user();
        $this->authorizeTemplateAccess($formTemplate);
        if (!$user->canManageFormTemplates()) {
            abort(403, 'You do not have permission to edit form templates.');
        }

        return view('forms.templates.edit', [
            'template' => $formTemplate,
            'allowedExtensions' => FormTemplate::allowedExtensions(),
        ]);
    }

    public function update(Request $request, FormTemplate $formTemplate)
    {
        $user = Auth::user();
        $this->authorizeTemplateAccess($formTemplate);
        if (!$user->canManageFormTemplates()) {
            abort(403, 'You do not have permission to update form templates.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'category' => 'nullable|string|max:100',
            'is_active' => 'nullable|boolean',
            'file' => 'nullable|file|mimes:doc,docx,xls,xlsx|max:' . config('app.concure.max_file_size'),
        ]);

        $data = [
            'name' => (string)$request->input('name'),
            'description' => $request->input('description'),
            'category' => $request->input('category'),
            'is_active' => $request->boolean('is_active', true),
            'updated_by' => $user->id,
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');

            // Delete old file if exists
            if ($formTemplate->file_path && Storage::disk('public')->exists($formTemplate->file_path)) {
                Storage::disk('public')->delete($formTemplate->file_path);
            }

            $originalName = $file->getClientOriginalName();
            $filename = time() . '_' . $originalName;
            $dir = FormTemplate::storageDirForClinic((int)($formTemplate->clinic_id ?: $user->clinic_id));
            $path = $file->storeAs($dir, $filename, 'public');

            $data['file_path'] = $path;
            $data['original_filename'] = $originalName;
            $data['file_type'] = strtolower($file->getClientOriginalExtension());
            $data['file_size'] = $file->getSize();
        }

        $formTemplate->update($data);

        return redirect()->route('forms.templates.index')
            ->with('success', __('Form template updated successfully.'));
    }

    public function destroy(FormTemplate $formTemplate)
    {
        $user = Auth::user();
        $this->authorizeTemplateAccess($formTemplate);
        if (!$user->canManageFormTemplates()) {
            abort(403, 'You do not have permission to delete form templates.');
        }

        $formTemplate->delete();

        return redirect()->route('forms.templates.index')
            ->with('success', __('Form template deleted.'));
    }

    public function download(FormTemplate $formTemplate)
    {
        $user = Auth::user();
        $this->authorizeTemplateAccess($formTemplate);
        if (!$user->canViewPatientForms() && !$user->canManageFormTemplates()) {
            abort(403, 'You do not have permission to download this template.');
        }

        $path = $formTemplate->file_path;
        if (!$path || !Storage::disk('public')->exists($path)) {
            return back()->with('error', __('File not found.'));
        }

        return Storage::disk('public')->download($path, $formTemplate->original_filename ?: basename($path));
    }

    private function authorizeTemplateAccess(FormTemplate $formTemplate): void
    {
        $user = Auth::user();
        if (!$user->isSuperAdmin()) {
            $userClinicId = (int)$user->clinic_id;
            $templateClinicId = (int)$formTemplate->clinic_id;
            if ($userClinicId && $templateClinicId && $userClinicId !== $templateClinicId) {
                abort(403, 'Unauthorized access to template.');
            }
        }
    }
}

