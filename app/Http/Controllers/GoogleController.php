<?php

namespace App\Http\Controllers;

use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Str;

class GoogleController extends Controller
{
    // Redirect the user to Google for authentication
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    // Handle the callback from Google
    public function handleGoogleCallback(Request $request)
    {
        try {
            // Get the user data from Google
            $googleUser = Socialite::driver('google')->user();

            // Check if the user already exists
            $user = User::where('google_id', $googleUser->id)->first();

            if ($user) {
                // If user exists, log them in and return success response
                Auth::login($user);
                return response()->json([
                    'message' => 'Login successful',
                    'user' => $user,
                    'token' => $user->createToken('digital-menus')->accessToken, 
                ]);
            } else {
                // If user doesn't exist, create a new one
                $newUser = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'password' => bcrypt(Str::random(16)),
                ]);

                Auth::login($newUser);

                return response()->json([
                    'message' => 'User registered and logged in',
                    'user' => $newUser,
                    'token' => $newUser->createToken('YourAppName')->accessToken,
                ]);
            }
        } catch (Exception $e) {
            // Handle error
            return response()->json(['error' => 'Google login failed'], 400);
        }
    }
} 

