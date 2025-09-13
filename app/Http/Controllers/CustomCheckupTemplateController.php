<?php

namespace App\Http\Controllers;

use App\Models\CustomCheckupTemplate;
use App\Models\PatientCheckupTemplateAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomCheckupTemplateController extends Controller
{
    /**
     * Display a listing of custom checkup templates.
     */
    public function index(Request $request)
    {
        // Handle bulk actions
        if ($request->has('bulk_action') && $request->has('template_ids')) {
            $this->handleBulkAction($request);
            return redirect()->route('admin.checkup-templates.index')
                            ->with('success', 'Bulk action completed successfully.');
        }

        $templates = CustomCheckupTemplate::forClinic(Auth::user()->clinic_id)
                                         ->with(['creator'])
                                         ->orderBy('is_default', 'desc')
                                         ->orderBy('medical_condition')
                                         ->orderBy('name')
                                         ->get();

        $specialties = $templates->pluck('specialty')->filter()->unique()->sort()->values();
        $conditions = $templates->pluck('medical_condition')->filter()->unique()->sort()->values();
        $checkupTypes = CustomCheckupTemplate::getCheckupTypes();

        return view('admin.checkup-templates.index', compact('templates', 'specialties', 'conditions', 'checkupTypes'));
    }

    /**
     * Show the form for creating a new template.
     */
    public function create()
    {
        $checkupTypes = CustomCheckupTemplate::getCheckupTypes();
        $fieldTypes = CustomCheckupTemplate::getFieldTypes();
        $defaultTemplates = CustomCheckupTemplate::getDefaultTemplates();

        return view('admin.checkup-templates.create', compact('checkupTypes', 'fieldTypes', 'defaultTemplates'));
    }

    /**
     * Store a newly created template.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'medical_condition' => 'nullable|string|max:255',
            'specialty' => 'nullable|string|max:255',
            'checkup_type' => 'required|string|in:' . implode(',', array_keys(CustomCheckupTemplate::getCheckupTypes())),
            'form_config' => 'required|array',
            'form_config.sections' => 'required|array|min:1',
            'is_default' => 'boolean',
        ]);

        $template = CustomCheckupTemplate::createFromFormBuilder($request->all(), Auth::user());

        return redirect()->route('admin.checkup-templates.index')
                        ->with('success', "Checkup template '{$template->name}' created successfully.");
    }

    /**
     * Display the specified template.
     */
    public function show(CustomCheckupTemplate $template)
    {
        $this->authorizeTemplateAccess($template);

        $template->load(['creator', 'patientAssignments.patient']);
        $usageStats = $template->usage_stats;

        return view('admin.checkup-templates.show', compact('template', 'usageStats'));
    }

    /**
     * Show the form for editing the template.
     */
    public function edit(CustomCheckupTemplate $template)
    {
        $this->authorizeTemplateAccess($template);

        $checkupTypes = CustomCheckupTemplate::getCheckupTypes();
        $fieldTypes = CustomCheckupTemplate::getFieldTypes();

        return view('admin.checkup-templates.edit', compact('template', 'checkupTypes', 'fieldTypes'));
    }

    /**
     * Update the specified template.
     */
    public function update(Request $request, CustomCheckupTemplate $template)
    {
        $this->authorizeTemplateAccess($template);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'medical_condition' => 'nullable|string|max:255',
            'specialty' => 'nullable|string|max:255',
            'checkup_type' => 'required|string|in:' . implode(',', array_keys(CustomCheckupTemplate::getCheckupTypes())),
            'form_config' => 'required|array',
            'form_config.sections' => 'required|array|min:1',
            'is_default' => 'boolean',
        ]);

        $template->update($request->all());

        return redirect()->route('admin.checkup-templates.index')
                        ->with('success', "Checkup template '{$template->name}' updated successfully.");
    }

    /**
     * Remove the specified template.
     */
    public function destroy(CustomCheckupTemplate $template)
    {
        $this->authorizeTemplateAccess($template);

        // Check if template is being used
        $assignmentsCount = $template->patientAssignments()->count();
        $checkupsCount = $template->checkups()->count();

        if ($assignmentsCount > 0 || $checkupsCount > 0) {
            return redirect()->route('admin.checkup-templates.index')
                            ->with('error', "Cannot delete template '{$template->name}' because it is assigned to {$assignmentsCount} patient(s) and has been used in {$checkupsCount} checkup(s).");
        }

        $templateName = $template->name;
        $template->delete();

        return redirect()->route('admin.checkup-templates.index')
                        ->with('success', "Checkup template '{$templateName}' deleted successfully.");
    }

    /**
     * Toggle template status.
     */
    public function toggleStatus(CustomCheckupTemplate $template)
    {
        $this->authorizeTemplateAccess($template);

        $template->update(['is_active' => !$template->is_active]);

        $status = $template->is_active ? 'activated' : 'deactivated';
        return redirect()->route('admin.checkup-templates.index')
                        ->with('success', "Template '{$template->name}' {$status} successfully.");
    }

    /**
     * Clone template.
     */
    public function clone(Request $request, CustomCheckupTemplate $template)
    {
        $this->authorizeTemplateAccess($template);

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $clonedTemplate = $template->cloneTemplate($request->name, Auth::user());

        return redirect()->route('admin.checkup-templates.edit', $clonedTemplate)
                        ->with('success', "Template cloned successfully as '{$clonedTemplate->name}'.");
    }

    /**
     * Get template preview for AJAX.
     */
    public function preview(CustomCheckupTemplate $template)
    {
        $this->authorizeTemplateAccess($template);

        return response()->json([
            'template' => $template,
            'form_sections' => $template->form_sections,
            'fields_count' => $template->fields_count,
            'sections_count' => $template->sections_count,
        ]);
    }

    /**
     * Handle bulk actions on templates.
     */
    private function handleBulkAction(Request $request)
    {
        $request->validate([
            'bulk_action' => 'required|in:activate,deactivate,delete',
            'template_ids' => 'required|array',
            'template_ids.*' => 'exists:custom_checkup_templates,id',
        ]);

        $templateIds = $request->template_ids;
        $action = $request->bulk_action;

        // Ensure user can only modify their clinic's templates
        $templates = CustomCheckupTemplate::whereIn('id', $templateIds)
                                         ->where('clinic_id', Auth::user()->clinic_id)
                                         ->get();

        foreach ($templates as $template) {
            switch ($action) {
                case 'activate':
                    $template->update(['is_active' => true]);
                    break;
                case 'deactivate':
                    $template->update(['is_active' => false]);
                    break;
                case 'delete':
                    // Only delete if not in use
                    if ($template->patientAssignments()->count() === 0 && $template->checkups()->count() === 0) {
                        $template->delete();
                    }
                    break;
            }
        }
    }

    /**
     * Authorize access to template.
     */
    private function authorizeTemplateAccess(CustomCheckupTemplate $template): void
    {
        // DEVELOPMENT MODE: Completely disable template access authorization
        if (config('app.debug') || env('DISABLE_PERMISSIONS', true)) {
            return; // Allow all access during development
        }

        $user = Auth::user();

        // Users can only access templates in their clinic
        if ($template->clinic_id !== $user->clinic_id) {
            abort(403, 'Unauthorized access to template.');
        }

        // Check permission-based access or role-based fallback
        if (!$user->hasPermission('settings_manage') &&
            !in_array($user->role, ['doctor', 'admin'])) {
            abort(403, 'Insufficient permissions to manage checkup templates.');
        }
    }
}
