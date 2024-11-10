<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SocialiteController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     *
     * @param string $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function authProviderRedirect($provider)
    {
        // Ensure the provider is valid
        if (!in_array($provider, ['google', 'github'])) {
            abort(404);
        }

        // Redirect the user to the chosen OAuth provider (Google or GitHub)
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Handle the callback from Google authentication.
     *
     * @param string $provider
     * @return \Illuminate\Http\RedirectResponse
     */
    public function socialAuthentication($provider)
    {
        try {
            // Get the user information from the OAuth provider (Google)
            $socialUser = Socialite::driver($provider)->user();

            // Check if a user already exists with this provider's ID
            $user = User::where('auth_provider_id', $socialUser->getId())->first();

            // If the user exists, log them in
            if ($user) {
                Auth::login($user);
            } else {
                // If the user does not exist, create a new user in the database
                $user = User::create([
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'password' => null, // No password required for OAuth users
                    'auth_provider' => $provider,
                    'auth_provider_id' => $socialUser->getId(),
                    'avatar' => $socialUser->getAvatar(), // Save the avatar URL
                ]);

                // Log the newly created user in
                Auth::login($user);
            }

            // After successful authentication, redirect to the dashboard
            return redirect()->route('dashboard');
        } catch (Exception $e) {
            // Handle any errors that occur during authentication
            return redirect()->route('login')->with('error', 'Authentication failed: ' . $e->getMessage());
        }
    }
}
