<?php

namespace App\Http\Controllers;

use App\Models\FormTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

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

        // Filter by creator for regular doctors
        if (!$user->isSuperAdmin() && !$user->isClinicAdmin()) {
            $query->byCreator($user->id);
        }

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
        $disk = Storage::disk('public');
        if (!$path || !$disk->exists($path)) {
            return redirect()->route('forms.templates.index')->with('error', __('File not found.'));
        }

        $absolutePath = $disk->path($path);
        $filename = $formTemplate->original_filename ?: basename($path);
        $mime = File::mimeType($absolutePath) ?: 'application/octet-stream';
        $size = $formTemplate->file_size ?: (@is_file($absolutePath) ? @filesize($absolutePath) : null);

        // Try to open stream first so any failure is handled here (not inside the response callback)
        $stream = @fopen($absolutePath, 'rb');
        if ($stream === false) {
            \Log::warning('Form template fopen failed; falling back to storage download', [
                'template_id' => $formTemplate->id,
                'path' => $path,
            ]);
            return $disk->download($path, $filename);
        }

        $headers = [
            'Content-Type' => $mime,
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ];
        if (!empty($size)) {
            $headers['Content-Length'] = $size;
        }

        try {
            return response()->streamDownload(function () use ($stream) {
                while (!feof($stream)) {
                    echo fread($stream, 1024 * 1024); // 1MB chunks
                    @ob_flush();
                    flush();
                }
                fclose($stream);
            }, $filename, $headers);
        } catch (\Throwable $e) {
            \Log::error('Form template download failed', [
                'template_id' => $formTemplate->id,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            if (is_resource($stream)) { @fclose($stream); }
            // Fallback to Laravel's Storage download
            return $disk->download($path, $filename);
        }
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

            // Regular doctors can only access their own templates
            if (!$user->isClinicAdmin() && $formTemplate->created_by !== $user->id) {
                abort(403, 'Unauthorized access to template.');
            }
        }
    }
}

