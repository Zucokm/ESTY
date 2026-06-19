<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class CategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     */
    public function index()
    {
        $categories = Category::withCount('products')->get();
        return Inertia::render('Admin/Categories/Index', [
            'categories' => $categories
        ]);
    }

    /**
     * Store a newly created category.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048', // 2MB Max
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('categories', 'public');
            $imagePath = '/storage/' . $path;
        }

        $category = Category::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? '',
            'image_path' => $imagePath,
        ]);

        // If it's an AJAX request (like our "+ New" quick modal), return JSON.
        // Otherwise, redirect back.
        if ($request->wantsJson()) {
            return response()->json($category);
        }

        return redirect()->back()->with('success', 'Category created successfully.');
    }

    /**
     * Update the specified category.
     */
    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048', // 2MB Max
        ]);

        $imagePath = $category->image_path;
        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($category->image_path && file_exists(public_path($category->image_path))) {
                @unlink(public_path($category->image_path));
            }

            $path = $request->file('image')->store('categories', 'public');
            $imagePath = '/storage/' . $path;
        }

        $category->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? '',
            'image_path' => $imagePath,
        ]);

        return redirect()->back()->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified category.
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        // Check if there are products in this category
        if ($category->products()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete category that contains products.');
        }

        // Delete image if it exists
        if ($category->image_path && file_exists(public_path($category->image_path))) {
            @unlink(public_path($category->image_path));
        }

        $category->delete();

        return redirect()->back()->with('success', 'Category deleted successfully.');
    }
}
