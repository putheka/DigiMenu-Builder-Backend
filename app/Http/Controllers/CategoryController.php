<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Menu;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // List categories for a specific menu
    public function index($menuId)
    {
        $categories = Category::where('menu_id', $menuId)->get();

        return response()->json([
            'status' => 'success',
            'status_code' => 200,
            'data' => $categories,
        ], 200);
    }

    // Create a new category for a specific menu
    public function store(Request $request, $menuId)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Ensure the menu exists
        $menu = Menu::findOrFail($menuId);

        // Create the category
        $category = $menu->categories()->create([
            'name' => $request->name,
        ]);

        return response()->json([
            'status' => 'success',
            'status_code' => 201,
            'message' => 'Category created successfully.',
            'data' => $category,
        ], 201);
    }

    // Update a category
    public function update(Request $request, $menuId, $categoryId)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category = Category::where('menu_id', $menuId)->findOrFail($categoryId);
        $category->update([
            'name' => $request->name,
        ]);

        return response()->json([
            'status' => 'success',
            'status_code' => 200,
            'message' => 'Category updated successfully.',
            'data' => $category,
        ], 200);
    }

    // Delete a category
    public function destroy($menuId, $categoryId)
    {
        $category = Category::where('menu_id', $menuId)->findOrFail($categoryId);
        $category->delete();

        return response()->json([
            'status' => 'success',
            'status_code' => 200,
            'message' => 'Category deleted successfully.',
        ], 200);
    }
}
