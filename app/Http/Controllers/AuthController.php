<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\RateLimiter;


class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Define rate limit key and limit threshold
        $throttleKey = 'login:' . $request->ip();
        $maxAttempts = 5; // Allow up to 5 attempts
        $decayMinutes = 1; // Lockout for 1 minute after max attempts

        // Check rate limit
        if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->with('toast_error', ['message' => "Too many login attempts. Please try again in $seconds seconds."]);
        }

        // Validate request input
        $request->validate([
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
        ]);

        // Prepare data for the API request
        $data = [
            'appName' => 'Appraisal',
            'user' => $request->input('username'),
            'password' => $request->input('password'),
            'validateAppAcess' => true
        ];


        try {
            // Send the POST request to the API
            $response = Http::withoutVerifying()->post('http://bp-ho-gcupdate.Bestpointgh.com:8093/verification/LocalAccount/UserNameOrPhoneOrEmailAndPassword', $data);

                // Check for a successful response and the presence of access token
                if ($response->successful() && isset($response['access_token'])) {
                    $data = $response->object();

                    // dd($data);

                    // Store access token and user profile data in the session
                    session([
                        'api_token' => $data->access_token,
                        // 'token_issued_at' => time(), // Initialize last activity time
                    'user_name' => $data->profile->fullName,
                    'user_email' => $data->profile->email,
                    'employee_id' => $data->profile->id,
                    'empRole' => 4,
                ]);


                // Clear rate limit on success
                RateLimiter::clear($throttleKey);

                return redirect()->intended('/dashboard')->with('toast_success', 'Logged in successfully');

            }

            // Increment the rate limit on failed login attempt
            RateLimiter::hit($throttleKey, $decayMinutes * 60);

            // Log the error if authentication fails
            Log::warning('Authentication failed for user', ['user' => $request->input('username')]);
            // Return error if authentication fails
            return redirect()->back()->with('toast_error', 'Invalid credentials. Please try again.');
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Log the exception details for connection issues
            Log::error('Connection error during authentication', [
                'user' => $request->input('username'),
                'error' => $e->getMessage(),
            ]);

            // Increment the rate limit on exception
            RateLimiter::hit($throttleKey, $decayMinutes * 60);

            // Return specific error message for no internet connection
            return redirect()->back()->with('toast_error', 'Something went wrong, check your internet and try again, <b>Or Contact Application Support</b>');
        } catch (\Exception $e) {
            // Log the exception details for other errors
            Log::error('Error during authentication', [
                'user' => $request->input('username'),
                'error' => $e->getMessage(),
            ]);

            // Increment the rate limit on exception
            RateLimiter::hit($throttleKey, $decayMinutes * 60);

            // Return a generic error message to the user
            return back()->with('toast_error', 'An error occurred. Please try again later.');
        }
    }
    public function loginx(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            return redirect()->route('dashboard')->with('toast_success', 'Successfully logged in');
        }

        return redirect()->route('login')->with('toast_error', 'Invalid credentials');
    }

    public function register(Request $request)
    {
        $user = User::create([
            'fullname' => $request->fullname,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);
        return redirect()->route('dashboard')->with('toast_success', 'Successfully registered');
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('login')->with('toast_success', 'Successfully logged out');
    }
}
