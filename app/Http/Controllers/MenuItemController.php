<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Exception;

class MenuItemController extends Controller
{

    private $UploadMediaController;

    public function __construct(UploadMediaController $uploadMediaController)
    {
        $this->UploadMediaController = $uploadMediaController;
    }

    // List all menu items for a specific category
    public function index($categoryId)
    {
        $menuItems = MenuItem::where('category_id', $categoryId)->get();

        return response()->json([
            'status' => 'success',
            'status_code' => 200,
            'data' => $menuItems,
        ], 200);
    }

    // Create a new menu item with image upload to S3
    public function store(Request $request, $categoryId)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'price' => 'required|numeric',
            'is_available' => 'sometimes|boolean',
            'file' => 'sometimes|array',
            'file.*' => 'image|mimes:jpg,jpeg,png|max:2048',
        ]);

        try {
            // Handle image upload if a file is provided
            $imageUrl = null;
            if ($request->hasFile('file')) {
                $uploadedImages = $this->uploadMediaController->uploadMedia($request);
                if (empty($uploadedImages)) {
                    throw new Exception('Image upload failed');
                }

                // Set the image URL to the first uploaded image URL
                $imageUrl = $uploadedImages[0];
            }

            // Create the new MenuItem
            $menuItem = MenuItem::create([
                'category_id' => $categoryId,
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'price' => $request->input('price'),
                'is_available' => $request->input('is_available', true), // Default to true if not provided
                'image_url' => $imageUrl,
            ]);

            return response()->json(['message' => 'Menu item created successfully', 'data' => $menuItem], 201);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to create menu item: ' . $e->getMessage()], 500);
        }
    }

    // Update an existing menu item
    public function update(Request $request, $categoryId, $menuItemId)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:1000',
            'price' => 'sometimes|numeric',
            'is_available' => 'sometimes|boolean',
            'file' => 'sometimes|array',
            'file.*' => 'image|mimes:jpg,jpeg,png|max:2048',
        ]);

        try {
            // Find the menu item in the specified category
            $menuItem = MenuItem::where('id', $menuItemId)->where('category_id', $categoryId)->firstOrFail();

            // Update fields if provided in the request
            if ($request->has('name')) {
                $menuItem->name = $request->input('name');
            }
            if ($request->has('description')) {
                $menuItem->description = $request->input('description');
            }
            if ($request->has('price')) {
                $menuItem->price = $request->input('price');
            }
            if ($request->has('is_available')) {
                $menuItem->is_available = $request->input('is_available');
            }

            // Handle image update if a new file is uploaded
            if ($request->hasFile('file')) {
                // Delete old image from storage
                if ($menuItem->image_url) {
                    Storage::disk('s3')->delete(parse_url($menuItem->image_url, PHP_URL_PATH));
                }

                // Upload the new image
                $uploadedImages = $this->uploadMediaController->uploadMedia($request);
                if (empty($uploadedImages)) {
                    throw new Exception('Image upload failed');
                }

                // Update the image URL to the first uploaded image URL
                $menuItem->image_url = $uploadedImages[0];
            }

            // Save the updated menu item
            $menuItem->save();

            return response()->json(['message' => 'Menu item updated successfully', 'data' => $menuItem], 200);
        } catch (Exception $e) {
            return response()->json(['error' => 'Failed to update menu item: ' . $e->getMessage()], 500);
        }
    }

    // Delete a menu item
    public function destroy($categoryId, $menuItemId)
    {
        $menuItem = MenuItem::where('category_id', $categoryId)->findOrFail($menuItemId);

        // Delete the image from S3 if it exists
        if ($menuItem->image_url) {
            $imagePath = parse_url($menuItem->image_url, PHP_URL_PATH);
            Storage::disk('s3')->delete($imagePath);
        }

        $menuItem->delete();

        return response()->json([
            'status' => 'success',
            'status_code' => 200,
            'message' => 'Menu item deleted successfully.',
        ], 200);
    }
}
