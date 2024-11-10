<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class MenuController extends Controller
{
    // Get all menus of the authenticated user
    public function index()
    {
        $menus = Menu::where('user_id', Auth::id())->get();

        return response()->json([
            'status' => 'success',
            'status_code' => 200,
            'message' => 'Menus retrieved successfully.',
            'data' => $menus
        ]);
    }

    // Store a new menu
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'location' => 'required|string',
            'contact' => 'required|string',
            'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'is_available' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'status_code' => 422,
                'message' => 'Validation error occurred.',
                'errors' => $validator->errors()
            ]);
        }

        $imagePath = $request->file('image')->store('menu_images', 'public');

        $menu = Menu::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'location' => $request->location,
            'contact' => $request->contact,
            'image_url' => Storage::url($imagePath),
            'is_available' => $request->is_available ?? true
        ]);

        return response()->json([
            'status' => 'success',
            'status_code' => 201,
            'message' => 'Menu created successfully.',
            'data' => $menu
        ]);
    }

    // Update a menu 
    public function update(Request $request, $id)
    {
       
        $userId = Auth::id();

        if (!$userId) {
            return response()->json([
                'status' => 'error',
                'status_code' => 401,
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // Retrieve the menu item for the authenticated user
        $menu = Menu::where('id', $id)->where('user_id', $userId)->first();

        if (!$menu) {
            return response()->json([
                'status' => 'error',
                'status_code' => 404,
                'message' => 'Menu not found or you do not have permission to update this item.',
            ], 404);
        }

        // Validate incoming data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string',
            'contact' => 'required|string',
            'is_available' => 'boolean',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Handle image upload if present
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('menu_images', 'public');
            $validatedData['image_url'] = $imagePath;
        }

        // Update the menu item
        $menu->update($validatedData);

        return response()->json([
            'status' => 'success',
            'status_code' => 200,
            'message' => 'Menu updated successfully.',
            'data' => $menu,
        ], 200);
    }

    // Delete a menu
    public function destroy($id)
    {
        $menu = Menu::where('id', $id)->where('user_id', Auth::id())->first();
        if (!$menu) {
            return response()->json([
                'status' => 'error',
                'status_code' => 403,
                'message' => 'You do not have permission to delete this menu.'
            ]);
        }

        Storage::delete($menu->image_url);
        $menu->delete();

        return response()->json([
            'status' => 'success',
            'status_code' => 200,
            'message' => 'Menu deleted successfully.'
        ]);
    }
}
