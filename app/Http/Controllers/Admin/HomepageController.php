<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroSlide;
use App\Models\Lookbook;
use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class HomepageController extends Controller
{
    /**
     * Display the homepage settings dashboard.
     */
    public function index()
    {
        $slides = HeroSlide::orderBy('sort_order')->get();
        $lookbooks = Lookbook::orderBy('sort_order')->get();
        
        // Pluck key-value settings
        $settingsRaw = Setting::all();
        $settings = [];
        foreach ($settingsRaw as $setting) {
            $settings[$setting->key] = $setting->value;
        }

        // Provide defaults if not set in DB
        $defaults = [
            'brand_story_title' => 'Crafting a Dialogue Between Material & Form.',
            'brand_story_text_1' => 'At ESTY, we believe garments should not simply be worn, but experienced.',
            'brand_story_text_2' => 'Every buttonhole, every shoulder drape, and every single stitch is carefully planned and executed.',
            'brand_story_stat_1_val' => '100%',
            'brand_story_stat_1_lbl' => 'Local Sourcing',
            'brand_story_stat_2_val' => 'Limited',
            'brand_story_stat_2_lbl' => 'Studio Run',
            'brand_story_image' => '/images/brand_story.png',
            'feature_1_title' => 'Free Express Shipping',
            'feature_1_subtitle' => 'On all domestic orders over 150,000 Ks.',
            'feature_1_icon' => 'truck',
            'feature_2_title' => 'Easy Returns',
            'feature_2_subtitle' => '30-day effortless swap collection service.',
            'feature_2_icon' => 'arrow-path',
            'feature_3_title' => 'Premium Quality',
            'feature_3_subtitle' => '100% sustainably grown materials.',
            'feature_3_icon' => 'check-badge',
            'feature_4_title' => 'Bespoke Adjustments',
            'feature_4_subtitle' => 'Tailored customization for select products.',
            'feature_4_icon' => 'adjustments',
        ];
        $settings = array_merge($defaults, $settings);

        return Inertia::render('Admin/Homepage/Index', [
            'slides' => $slides,
            'lookbooks' => $lookbooks,
            'settings' => $settings
        ]);
    }

    /**
     * Store a new hero slide.
     */
    public function storeSlide(Request $request)
    {
        $validated = $request->validate([
            'badge' => 'nullable|string|max:255',
            'title' => 'required|string|max:255',
            'highlight' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'cta_text' => 'nullable|string|max:255',
            'cta_link' => 'nullable|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // max 5MB
            'sort_order' => 'required|integer',
            'is_active' => 'required|boolean',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('homepage', 'public');
            $validated['image_path'] = '/storage/' . $path;
        }

        HeroSlide::create($validated);

        Cache::forget('esty_active_slides');
        return redirect()->back()->with('success', 'Hero slide created successfully.');
    }

    /**
     * Update an existing hero slide.
     */
    public function updateSlide(Request $request, $id)
    {
        $slide = HeroSlide::findOrFail($id);

        $validated = $request->validate([
            'badge' => 'nullable|string|max:255',
            'title' => 'required|string|max:255',
            'highlight' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'cta_text' => 'nullable|string|max:255',
            'cta_link' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'sort_order' => 'required|integer',
            'is_active' => 'required|boolean',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image if it is user-uploaded in public storage
            if (str_starts_with($slide->image_path, '/storage/')) {
                $oldPath = str_replace('/storage/', '', $slide->image_path);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('image')->store('homepage', 'public');
            $validated['image_path'] = '/storage/' . $path;
        }

        $slide->update($validated);

        Cache::forget('esty_active_slides');
        return redirect()->back()->with('success', 'Hero slide updated successfully.');
    }

    /**
     * Delete a hero slide.
     */
    public function destroySlide($id)
    {
        $slide = HeroSlide::findOrFail($id);

        // Delete image if it is user-uploaded
        if (str_starts_with($slide->image_path, '/storage/')) {
            $oldPath = str_replace('/storage/', '', $slide->image_path);
            Storage::disk('public')->delete($oldPath);
        }

        $slide->delete();

        Cache::forget('esty_active_slides');
        return redirect()->back()->with('success', 'Hero slide deleted successfully.');
    }

    /**
     * Store a new lookbook card.
     */
    public function storeLookbook(Request $request)
    {
        $validated = $request->validate([
            'badge' => 'nullable|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cta_text' => 'nullable|string|max:255',
            'cta_link' => 'nullable|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'sort_order' => 'required|integer',
            'is_active' => 'required|boolean',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('homepage', 'public');
            $validated['image_path'] = '/storage/' . $path;
        }

        Lookbook::create($validated);

        Cache::forget('esty_active_lookbooks');
        return redirect()->back()->with('success', 'Lookbook card created successfully.');
    }

    /**
     * Update an existing lookbook card.
     */
    public function updateLookbook(Request $request, $id)
    {
        $lookbook = Lookbook::findOrFail($id);

        $validated = $request->validate([
            'badge' => 'nullable|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cta_text' => 'nullable|string|max:255',
            'cta_link' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'sort_order' => 'required|integer',
            'is_active' => 'required|boolean',
        ]);

        if ($request->hasFile('image')) {
            if (str_starts_with($lookbook->image_path, '/storage/')) {
                $oldPath = str_replace('/storage/', '', $lookbook->image_path);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('image')->store('homepage', 'public');
            $validated['image_path'] = '/storage/' . $path;
        }

        $lookbook->update($validated);

        Cache::forget('esty_active_lookbooks');
        return redirect()->back()->with('success', 'Lookbook card updated successfully.');
    }

    /**
     * Delete a lookbook card.
     */
    public function destroyLookbook($id)
    {
        $lookbook = Lookbook::findOrFail($id);

        if (str_starts_with($lookbook->image_path, '/storage/')) {
            $oldPath = str_replace('/storage/', '', $lookbook->image_path);
            Storage::disk('public')->delete($oldPath);
        }

        $lookbook->delete();

        Cache::forget('esty_active_lookbooks');
        return redirect()->back()->with('success', 'Lookbook card deleted successfully.');
    }

    /**
     * Update Brand Philosophy / Story Settings.
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'brand_story_title' => 'nullable|string|max:255',
            'brand_story_text_1' => 'nullable|string',
            'brand_story_text_2' => 'nullable|string',
            'brand_story_stat_1_val' => 'nullable|string|max:255',
            'brand_story_stat_1_lbl' => 'nullable|string|max:255',
            'brand_story_stat_2_val' => 'nullable|string|max:255',
            'brand_story_stat_2_lbl' => 'nullable|string|max:255',
            'brand_story_image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'feature_1_title' => 'nullable|string|max:255',
            'feature_1_subtitle' => 'nullable|string|max:255',
            'feature_1_icon' => 'nullable|string|max:255',
            'feature_2_title' => 'nullable|string|max:255',
            'feature_2_subtitle' => 'nullable|string|max:255',
            'feature_2_icon' => 'nullable|string|max:255',
            'feature_3_title' => 'nullable|string|max:255',
            'feature_3_subtitle' => 'nullable|string|max:255',
            'feature_3_icon' => 'nullable|string|max:255',
            'feature_4_title' => 'nullable|string|max:255',
            'feature_4_subtitle' => 'nullable|string|max:255',
            'feature_4_icon' => 'nullable|string|max:255',
        ]);

        // Process settings updates
        foreach ($validated as $key => $val) {
            if ($key === 'brand_story_image_file') continue;
            if ($val !== null) {
                Setting::set($key, $val);
            }
        }


        if ($request->hasFile('brand_story_image_file')) {
            $currentImage = Setting::get('brand_story_image');
            if ($currentImage && str_starts_with($currentImage, '/storage/')) {
                $oldPath = str_replace('/storage/', '', $currentImage);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('brand_story_image_file')->store('homepage', 'public');
            Setting::set('brand_story_image', '/storage/' . $path);
        }

        Cache::forget('esty_homepage_settings');
        return redirect()->back()->with('success', 'Brand philosophy settings updated successfully.');
    }
}
