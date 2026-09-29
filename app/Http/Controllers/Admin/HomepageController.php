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
            'brand_story_title' => '',
            'brand_story_text_1' => '',
            'brand_story_text_2' => '',
            'brand_story_stat_1_val' => '',
            'brand_story_stat_1_lbl' => '',
            'brand_story_stat_2_val' => '',
            'brand_story_stat_2_lbl' => '',
            'brand_story_image' => '',
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
            'brand_story_title' => 'required|string|max:255',
            'brand_story_text_1' => 'required|string',
            'brand_story_text_2' => 'required|string',
            'brand_story_stat_1_val' => 'required|string|max:255',
            'brand_story_stat_1_lbl' => 'required|string|max:255',
            'brand_story_stat_2_val' => 'required|string|max:255',
            'brand_story_stat_2_lbl' => 'required|string|max:255',
            'brand_story_image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'feature_1_title' => 'required|string|max:255',
            'feature_1_subtitle' => 'required|string|max:255',
            'feature_1_icon' => 'required|string|max:255',
            'feature_2_title' => 'required|string|max:255',
            'feature_2_subtitle' => 'required|string|max:255',
            'feature_2_icon' => 'required|string|max:255',
            'feature_3_title' => 'required|string|max:255',
            'feature_3_subtitle' => 'required|string|max:255',
            'feature_3_icon' => 'required|string|max:255',
            'feature_4_title' => 'required|string|max:255',
            'feature_4_subtitle' => 'required|string|max:255',
            'feature_4_icon' => 'required|string|max:255',

        ]);

        // Process settings updates
        Setting::set('brand_story_title', $validated['brand_story_title']);
        Setting::set('brand_story_text_1', $validated['brand_story_text_1']);
        Setting::set('brand_story_text_2', $validated['brand_story_text_2']);
        Setting::set('brand_story_stat_1_val', $validated['brand_story_stat_1_val']);
        Setting::set('brand_story_stat_1_lbl', $validated['brand_story_stat_1_lbl']);
        Setting::set('brand_story_stat_2_val', $validated['brand_story_stat_2_val']);
        Setting::set('brand_story_stat_2_lbl', $validated['brand_story_stat_2_lbl']);
        Setting::set('feature_1_title', $validated['feature_1_title']);
        Setting::set('feature_1_subtitle', $validated['feature_1_subtitle']);
        Setting::set('feature_1_icon', $validated['feature_1_icon']);
        Setting::set('feature_2_title', $validated['feature_2_title']);
        Setting::set('feature_2_subtitle', $validated['feature_2_subtitle']);
        Setting::set('feature_2_icon', $validated['feature_2_icon']);
        Setting::set('feature_3_title', $validated['feature_3_title']);
        Setting::set('feature_3_subtitle', $validated['feature_3_subtitle']);
        Setting::set('feature_3_icon', $validated['feature_3_icon']);
        Setting::set('feature_4_title', $validated['feature_4_title']);
        Setting::set('feature_4_subtitle', $validated['feature_4_subtitle']);
        Setting::set('feature_4_icon', $validated['feature_4_icon']);


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
