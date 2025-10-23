<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\RequestException;
use App\Services\AuditorApiService;
use App\Http\Traits\HandlesApiErrors;

class ExceptionStatusChange extends Controller
{
    use HandlesApiErrors;

    protected AuditorApiService $apiService;

    public function __construct(AuditorApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function exceptionPushBackToAuditor(Request $request)
    {
        try {
            // Validate request data
            $validated = $request->validate([
                'status' => 'nullable|string',
                'statusComment' => 'nullable|string',
                'exceptionId' => 'required|integer',
                // 'requestTrackerId' => 'nullable|integer',
                // 'requestType' => 'nullable|string',
            ]);

            if (!$this->hasValidApiToken()) {
                return $this->redirectToLoginIfNoToken();
            }

            $data = [
                'singleExceptionId' => $validated['exceptionId'],
                'status' => $validated['status'],
                'statusComment' => $validated['statusComment'],
                // 'requestTrackerId' => $validated['requestTrackerId'] ?? null,
                // 'requestType' => $validated['requestType'] ?? null,
            ];

            $updateResponse = $this->apiService->put(
                $this->apiService->getEndpoint('update_single_exception_status'),
                $data,
                $this->getApiToken()
            );

            if ($updateResponse->successful()) {
                Log::info('Exception push back to auditor successful', ['exception_id' => $validated['exceptionId']]);
                return redirect()->route('auditee.pending.exception.list')->with('toast_success', 'Exception pushed back to auditor successfully');
            } else {
                Log::error('Failed to push back Exception to auditor', [
                    'status' => $updateResponse->status(),
                    'exception_id' => $validated['exceptionId']
                ]);
                return redirect()->back()->with('toast_error', 'Sorry, failed to push back Exception to auditor ' . $updateResponse);
            }
        } catch (RequestException $e) {
            Log::error('HTTP request failed for exception push back', [
                'message' => $e->getMessage(),
                'exception_id' => $request->input('exceptionId')
            ]);
            return redirect()->back()->with('toast_error', 'Network error occurred. Please try again later.');
        } catch (\Exception $e) {
            Log::error('Unexpected error in exception push back', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'exception_id' => $request->input('exceptionId')
            ]);
            return redirect()->back()->with('toast_error', 'An unexpected error occurred. Please try again.');
        }
    }


    public function recommendExceptionForResolution(Request $request, string $id)
    {
        $request->validate([
            'resolution' => 'required|string'
        ]);

        if (!$this->hasValidApiToken()) {
            return $this->redirectToLoginIfNoToken();
        }

        $data = [
            'id' => $id,
            'recommendedStatus' => $request->input('resolution')
        ];

        try {
            // Make the PUT request to the external API
            $response = $this->apiService->put(
                $this->apiService->getEndpoint('exception_auditee_update'),
                $data,
                $this->getApiToken()
            );

            // Check the response status and return appropriate response
            if ($response->successful()) {
                return redirect()->route('exception.list')->with('toast_success', 'Exception recommended for resolution successfully');
            } else {
                // Log the error response
                Log::error('Failed to recommended for resolution Exception', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return redirect()->back()->with('toast_error', 'Sorry, failed to recommended for resolution Exception: ' . $response->body());
            }
        } catch (\Exception $e) {
            // Log the exception
            Log::error('Exception occurred while recommending for resolution Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('toast_error', 'Something went wrong, check your internet and try again, <b>Or Contact Application Support</b>');
        }
    }

    public function closeException(Request $request, string $id)
    {
        if (!$this->hasValidApiToken()) {
            return $this->redirectToLoginIfNoToken();
        }

        $data = [
            'id' => $id
        ];

        try {
            // Make the PUT request to the external API
            $response = $this->apiService->put(
                $this->apiService->getEndpoint('exception_close'),
                $data,
                $this->getApiToken()
            );

            // Check the response status and return appropriate response
            if ($response->successful()) {
                if (URL::current() == route('exception.list')) {
                    return redirect()->route('exception.list')->with('toast_success', 'Exception closed successfully');
                } else {
                    return redirect()->route('exception.pending')->with('toast_success', 'Exception closed successfully');
                }
            } else {
                // Log the error response
                Log::error('Failed to close Exception', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return redirect()->back()->with('toast_error', 'Sorry, failed to close Exception: ' . $response->body());
            }
        } catch (\Exception $e) {
            // Log the exception
            Log::error('Exception occurred while closing Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('toast_error', 'Something went wrong, check your internet and try again, <b>Or Contact Application Support</b>');
        }
    }



}
