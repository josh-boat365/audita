<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use App\Services\AuditorApiService;


class AuthController extends Controller
{
    protected AuditorApiService $apiService;

    public function __construct(AuditorApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function login(Request $request)
    {
        // Define rate limit key and limit threshold
        $throttleKey = 'login:' . $request->ip();
        $maxAttempts = 5; // Allow up to 5 attempts
        $decayMinutes = 1; // Lockout for 1 minute after max attempts

        // Check rate limit
        if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->with('toast_error', "Too many login attempts. Please try again in $seconds seconds.");
        }

        // Validate request input
        $request->validate([
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
        ]);

        try {
            // Use the centralized API service for authentication
            $result = $this->apiService->login(
                $request->input('username'),
                $request->input('password'),
                true
            );

            // Check if login was successful
            if ($result['success']) {
                $data = $result['data'];

                // Store access token and user profile data in the session
                session([
                    'api_token' => $data->access_token,
                    'user_name' => $data->profile->fullName,
                    'user_email' => $data->profile->email,
                    'employee_id' => $data->profile->id,
                    'empRole' => 4,
                ]);

                // Clear rate limit on success
                RateLimiter::clear($throttleKey);

                // Top managers role IDs
                // 1 - Managing Director
                // 2 - Head of Internal Audit
                // 4 - Head of Internal Control & Compliance
                $topManagers = [1, 2, 4];
                $employeeRoleId = ExceptionManipulationController::getLoggedInUserInformation()->empRoleId;
                $employeeId = ExceptionManipulationController::getLoggedInUserInformation()->id;

                if (in_array($employeeRoleId, $topManagers)) {
                    return redirect()->intended('/dashboard')->with('toast_success', 'Logged in successfully');
                }

                return redirect()->route('my.group.dashboard', $employeeId)
                    ->with('toast_success', 'Logged in successfully');
            }

            // Increment the rate limit on failed login attempt
            RateLimiter::hit($throttleKey, $decayMinutes * 60);

            // Return error message from API service
            return redirect()->back()->with('toast_error', $result['error']);

        } catch (\Exception $e) {
            // Log the exception details
            Log::error('Error during authentication', [
                'user' => $request->input('username'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Increment the rate limit on exception
            RateLimiter::hit($throttleKey, $decayMinutes * 60);

            // Return a generic error message to the user
            return back()->with('toast_error', 'An error occurred. Please try again later.');
        }
    }



    /**
     * Handle the incoming request to get the auth token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function getAuthToken(Request $request)
    {
        try {

            // Validate the query string parameters
            $validatedData = $request->validate([
                'access_token' => 'required|string',
                'fullName'     => 'required|string',
                'email'        => 'required|email',
                'id'           => 'required',
            ]);

            // Prepare user data from the validated query data
            $userData = [
                'api_token'   => $validatedData['access_token'],
                'user_name'   => $validatedData['fullName'],
                'user_email'  => $validatedData['email'],
                'employee_id' => $validatedData['id'],
            ];

            // Additional logic like user session creation or updating records can go here
            session([
                'api_token'   => $userData['api_token'],
                'user_name'   => $userData['user_name'],
                'user_email'  => $userData['user_email'],
                'employee_id' => $userData['employee_id'],
            ]);
            // Return a JSON response back to the requester
            // return response()->json([
            //     'message' => 'User authenticated successfully',
            //     'data'    => $userData,
            // ], 200);

            $topManagers = [1, 2, 4];
            $employeeRoleId = ExceptionManipulationController::getLoggedInUserInformation()->empRoleId;
            $employeeId = ExceptionManipulationController::getLoggedInUserInformation()->id;

            if (in_array($employeeRoleId, $topManagers)) {
                return redirect()->intended('/dashboard')->with('toast_success', 'Logged in successfully');
            }

            return redirect()->route('my.group.dashboard', $employeeId)->with('toast_success', 'Logged in successfully');

        } catch (ValidationException $e) {
            // Catch and return validation errors in a structured format
            Log::error('Validation error', [
                'errors' => $e->errors(),
            ]);
            return redirect()->back()->with('toast_error', 'Validation failed. Please check your input.');
        } catch (\Exception $e) {
            // Catch any other exceptions and return an error message
            Log::error('Error during authentication', [
                'error' => $e->getMessage(),
            ]);
            return redirect()->back()->with('toast_error', 'An error occurred. Please try again later.');
        }
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
