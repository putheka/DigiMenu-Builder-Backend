<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SocialiteController;
use App\Http\Controllers\Authentication;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MenuItemController;

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/upload', \App\Http\Controllers\UploadMediaController::class);



Route::post('/register', [Authentication::class, 'register']);
Route::post('/login', [Authentication::class, 'login']);

Route::controller(SocialiteController::class)->group(function() {
    Route::get('auth/redirection/{provider}', 'authProviderRedirect')->name('auth.redirection');
    Route::get('auth/{provider}/callback', 'socialAuthentication')->name('auth.callback');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/all/menus', [MenuController::class, 'index']);
    Route::post('/menus', [MenuController::class, 'store']);
    Route::put('/menus/{id}', [MenuController::class, 'update']);
    Route::delete('/menus/{id}', [MenuController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->group(function () {
    // List categories for a specific menu
    Route::get('/menus/{menuId}/categories', [CategoryController::class, 'index']);

    // Create a new category for a specific menu
    Route::post('/menus/{menuId}/categories', [CategoryController::class, 'store']);

    // Update a category
    Route::put('/menus/{menuId}/categories/{categoryId}', [CategoryController::class, 'update']);

    // Delete a category
    Route::delete('/menus/{menuId}/categories/{categoryId}', [CategoryController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/categories/{categoryId}/menu-items', [MenuItemController::class, 'index']);
    Route::post('/categories/{categoryId}/menu-items', [MenuItemController::class, 'store']);
    Route::put('/categories/{categoryId}/menu-items/{menuItemId}', [MenuItemController::class, 'update']);
    Route::delete('/categories/{categoryId}/menu-items/{menuItemId}', [MenuItemController::class, 'destroy']);
});
