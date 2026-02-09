<?php

namespace App\Http\Controllers;

use App\Models\Advertisement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdvertisementController extends Controller
{
    /**
     * Display a listing of advertisements.
     */
    public function index(Request $request)
    {
        $this->authorize('manage-advertisements');
        
        $user = auth()->user();
        
        $query = Advertisement::with(['clinic', 'creator']);

        // Filter by clinic for all users
        $query->where('clinic_id', $user->clinic_id);

        // Apply filters
        if ($request->filled('type')) {
            $query->byType($request->type);
        }

        if ($request->filled('position')) {
            $query->byPosition($request->position);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->currentlyActive();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status === 'expired') {
                $query->expired();
            }
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $advertisements = $query->ordered()->paginate(15);

        return view('advertisements.index', compact('advertisements'));
    }

    /**
     * Show the form for creating a new advertisement.
     */
    public function create()
    {
        $this->authorize('manage-advertisements');
        
        return view('advertisements.create');
    }

    /**
     * Store a newly created advertisement.
     */
    public function store(Request $request)
    {
        $this->authorize('manage-advertisements');
        
        $user = auth()->user();
        
        $request->validate([
            'title' => 'required|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'title_ar' => 'nullable|string|max:255',
            'title_ku' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'description_ku' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'link_url' => 'nullable|url',
            'type' => 'required|in:banner,popup,sidebar,footer,notification',
            'position' => 'required|in:top,middle,bottom,left,right,center',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'target_audience' => 'nullable|array',
            'target_audience.*' => 'in:all,patients,staff,doctors,new_patients',
            'priority' => 'nullable|integer|min:0|max:100',
        ]);

        // Prepare translations
        $titleTranslations = [];
        $descriptionTranslations = [];
        
        foreach (['en', 'ar', 'ku'] as $locale) {
            if ($request->filled("title_{$locale}")) {
                $titleTranslations[$locale] = $request->input("title_{$locale}");
            }
            if ($request->filled("description_{$locale}")) {
                $descriptionTranslations[$locale] = $request->input("description_{$locale}");
            }
        }

        $advertisementData = [
            'title' => $request->title,
            'title_translations' => $titleTranslations,
            'description' => $request->description,
            'description_translations' => $descriptionTranslations,
            'link_url' => $request->link_url,
            'type' => $request->type,
            'position' => $request->position,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'target_audience' => $request->target_audience ?? ['all'],
            'priority' => $request->priority ?? 50,
            'clinic_id' => $user->clinic_id,
            'created_by' => $user->id,
            'is_active' => true,
            'click_count' => 0,
            'view_count' => 0,
        ];

        // Handle image upload
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs("advertisements/{$user->clinic_id}", $filename, 'public');
            $advertisementData['image_path'] = $path;
        }

        $advertisement = Advertisement::create($advertisementData);

        return redirect()->route('advertisements.index')
                        ->with('success', 'Advertisement created successfully.');
    }

    /**
     * Display the specified advertisement.
     */
    public function show(Advertisement $advertisement)
    {
        $this->authorize('manage-advertisements');
        $this->authorizeAdvertisementAccess($advertisement);
        
        $advertisement->load(['clinic', 'creator']);
        
        return view('advertisements.show', compact('advertisement'));
    }

    /**
     * Show the form for editing the specified advertisement.
     */
    public function edit(Advertisement $advertisement)
    {
        $this->authorize('manage-advertisements');
        $this->authorizeAdvertisementAccess($advertisement);
        
        return view('advertisements.edit', compact('advertisement'));
    }

    /**
     * Update the specified advertisement.
     */
    public function update(Request $request, Advertisement $advertisement)
    {
        $this->authorize('manage-advertisements');
        $this->authorizeAdvertisementAccess($advertisement);
        
        $request->validate([
            'title' => 'required|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'title_ar' => 'nullable|string|max:255',
            'title_ku' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_en' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'description_ku' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'link_url' => 'nullable|url',
            'type' => 'required|in:banner,popup,sidebar,footer,notification',
            'position' => 'required|in:top,middle,bottom,left,right,center',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after:start_date',
            'target_audience' => 'nullable|array',
            'target_audience.*' => 'in:all,patients,staff,doctors,new_patients',
            'priority' => 'nullable|integer|min:0|max:100',
            'is_active' => 'boolean',
        ]);

        // Prepare translations
        $titleTranslations = $advertisement->title_translations ?? [];
        $descriptionTranslations = $advertisement->description_translations ?? [];
        
        foreach (['en', 'ar', 'ku'] as $locale) {
            if ($request->filled("title_{$locale}")) {
                $titleTranslations[$locale] = $request->input("title_{$locale}");
            }
            if ($request->filled("description_{$locale}")) {
                $descriptionTranslations[$locale] = $request->input("description_{$locale}");
            }
        }

        $updateData = [
            'title' => $request->title,
            'title_translations' => $titleTranslations,
            'description' => $request->description,
            'description_translations' => $descriptionTranslations,
            'link_url' => $request->link_url,
            'type' => $request->type,
            'position' => $request->position,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'target_audience' => $request->target_audience ?? ['all'],
            'priority' => $request->priority ?? 50,
            'is_active' => $request->boolean('is_active', true),
        ];

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($advertisement->image_path && Storage::exists($advertisement->image_path)) {
                Storage::delete($advertisement->image_path);
            }
            
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs("advertisements/{$advertisement->clinic_id}", $filename, 'public');
            $updateData['image_path'] = $path;
        }

        $advertisement->update($updateData);

        return redirect()->route('advertisements.index')
                        ->with('success', 'Advertisement updated successfully.');
    }

    /**
     * Remove the specified advertisement.
     */
    public function destroy(Advertisement $advertisement)
    {
        $this->authorize('manage-advertisements');
        $this->authorizeAdvertisementAccess($advertisement);
        
        $advertisement->delete();

        return redirect()->route('advertisements.index')
                        ->with('success', 'Advertisement deleted successfully.');
    }

    /**
     * Toggle advertisement status.
     */
    public function toggleStatus(Advertisement $advertisement)
    {
        $this->authorize('manage-advertisements');
        $this->authorizeAdvertisementAccess($advertisement);
        
        $advertisement->update(['is_active' => !$advertisement->is_active]);
        
        $status = $advertisement->is_active ? 'activated' : 'deactivated';
        
        return back()->with('success', "Advertisement {$status} successfully.");
    }

    /**
     * Track advertisement click.
     */
    public function trackClick(Advertisement $advertisement)
    {
        if ($advertisement->isCurrentlyActive()) {
            $advertisement->incrementClicks();
        }
        
        if ($advertisement->link_url) {
            return redirect($advertisement->link_url);
        }
        
        return back();
    }

    /**
     * Get advertisements for display.
     */
    public function getForDisplay(Request $request)
    {
        $type = $request->get('type', 'banner');
        $position = $request->get('position');
        $audience = $request->get('audience', 'all');
        
        $query = Advertisement::currentlyActive()
                             ->byType($type)
                             ->forAudience($audience);
        
        if ($position) {
            $query->byPosition($position);
        }
        
        $advertisements = $query->ordered()->get();
        
        // Increment view count for each advertisement
        foreach ($advertisements as $ad) {
            $ad->incrementViews();
        }
        
        return response()->json($advertisements->map(function ($ad) {
            return [
                'id' => $ad->id,
                'title' => $ad->translated_title,
                'description' => $ad->translated_description,
                'image_url' => $ad->image_url,
                'link_url' => $ad->link_url,
                'type' => $ad->type,
                'position' => $ad->position,
                'click_url' => route('advertisements.click', $ad),
            ];
        }));
    }

    /**
     * Authorize access to advertisement.
     */
    private function authorizeAdvertisementAccess(Advertisement $advertisement): void
    {
        $user = auth()->user();
        
        // Program owner can access all advertisements
        

        // Users can only access advertisements in their clinic
        if ($advertisement->clinic_id !== $user->clinic_id) {
            abort(403, 'Unauthorized access to advertisement.');
        }
    }
}
