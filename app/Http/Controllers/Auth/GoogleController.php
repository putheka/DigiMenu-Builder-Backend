<!-- 

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GoogleController extends Controller
{
    // Redirect to Google OAuth
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    // Handle the callback from Google
    public function handleGoogleCallback()
    {
        try {
            // Retrieve user from Google
            $googleUser = Socialite::driver('google')->stateless()->user();
            
            // Check if the user exists in the database
            $user = User::where('email', $googleUser->getEmail())->first();

            // If the user doesn't exist, create a new one
            if (!$user) {
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                ]);
            }

            // Log the user in
            Auth::login($user);

            // Create a token for the user
            $token = $user->createToken('YourAppName')->accessToken;

            // Return the token to the frontend
            return response()->json(['token' => $token]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Google login failed'], 500);
        }
    }
} -->
