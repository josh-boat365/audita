<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ChatsAndCommentsController extends Controller
{

    public function storeComment(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:255',
        ]);

        $access_token = session('api_token');

        $data = [
            'exceptionTrackerId' => $id,
            'comment' => $request->input('comment'),
        ];

        // dd($data);

        try {
            $response = Http::withToken($access_token)->post('http://192.168.1.200:5126/Auditor/ExceptionComment', $data);

            if ($response->successful()) {
                return redirect()->back()->with('toast_success', 'Comment added successfully');
            } else {
                // Log the error response
                Log::error('Failed to add comment', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return redirect()->back()->with('toast_error', 'Sorry, failed to add comment');
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Handle connection errors (e.g., API server is down)
            Log::error('Connection Error: Unable to reach Exception comment API', ['error' => $e->getMessage()]);

            toast('Failed to connect to the server. Please check your internet or try again later.', 'error');
            return [];
        } catch (\Exception $e) {
            Log::error('Exception occurred while adding comment', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('toast_error', 'Something went wrong, check your internet and try again, <b>Or Contact Application Support</b>');
        }
    }


    public function updateComment(Request $request, $id)
    {
        // dd($request->all());
        $request->validate([
            'comment' => 'required|string|max:255',
            'exceptionTrackerId' => 'required|integer',
        ]);

        $access_token = session('api_token');

        $data = [
            'id' => $id,
            'exceptionTrackerId' => $request->input('exceptionTrackerId'),
            'comment' => $request->input('comment'),
        ];


        try {
            $response = Http::withToken($access_token)->put('http://192.168.1.200:5126/Auditor/ExceptionComment', $data);

            if ($response->successful()) {
                return redirect()->back()->with('toast_success', 'Comment updated successfully');
            } else {
                // Log the error response
                Log::error('Failed to update comment', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                    'comment_id' => $id
                ]);
                return redirect()->back()->with('toast_error', 'Sorry, failed to update comment');
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Handle connection errors (e.g., API server is down)
            Log::error('Connection Error: Unable to reach Exception comment API for update', [
                'error' => $e->getMessage(),
                'comment_id' => $id
            ]);

            toast('Failed to connect to the server. Please check your internet or try again later.', 'error');
            return redirect()->back();
        } catch (\Exception $e) {
            Log::error('Exception occurred while updating comment', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'comment_id' => $id
            ]);
            return redirect()->back()->with('toast_error', 'Something went wrong, check your internet and try again, <b>Or Contact Application Support</b>');
        }
    }


    public function deleteComment($id)
    {
        $access_token = session('api_token');

        try {
            // Make the DELETE request to the external API
            $response = Http::withToken($access_token)
                ->delete("http://192.168.1.200:5126/Auditor/ExceptionComment/{$id}");

            // Check the response status and return appropriate response
            if ($response->successful()) {
                return redirect()->back()->with('toast_success', 'Exception Comment Deleted successfully');
            } else {
                // Log the error response
                Log::error('Failed to delete Exception Comment', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return redirect()->back()->with('toast_error', 'Sorry, failed to delete Exception Comment');
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Handle connection errors (e.g., API server is down)
            Log::error('Connection Error: Unable to reach Exception Comment delete API', ['error' => $e->getMessage()]);

            toast('Failed to connect to the server. Please check your internet or try again later.', 'error');
            return [];
        } catch (\Exception $e) {
            // Log the exception
            Log::error('Exception occurred while deleting Exception Comment', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('toast_error', 'Something went wrong, check your internet and try again, <b>Or Contact Application Support</b>');
        }
    }

    public static function getExceptionComments($exceptionId)
    {
        $access_token = session('api_token');

        try {
            $response = Http::withToken($access_token)->get('http://192.168.1.200:5126/Auditor/ExceptionComment');

            if ($response->successful()) {

                $api_response = $response->object() ?? [];
                $comments = collect($api_response)->filter(fn($comment) => $comment->exceptionTrackerId == $exceptionId)->all() ?? [];
            } elseif ($response->status() == 404) {
                $comments = [];
                Log::warning('Exception comments API returned 404 Not Found');
                toast('Exception comments data not found', 'warning');
            } else {
                $comments = [];
                Log::error('Exception comments API request failed', ['status' => $response->status()]);
                toast('Error fetching exception comments data', 'error');
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Handle connection errors (e.g., API server is down)
            Log::error('Connection Error: Unable to reach Exception comment API', ['error' => $e->getMessage()]);

            toast('Failed to connect to the server. Please check your internet or try again later.', 'error');
            return [];
        } catch (\Exception $e) {
            $comments = [];
            Log::error('Error fetching exception comments', ['error' => $e->getMessage()]);
            toast('An error occurred. Please try again later', 'error');
        }
        return $comments;
    }


    public function exceptionFileUpload(Request $request, $id)
    {
        $validated = $request->validate([
            'files' => 'nullable|array',
            'files.*' => 'file|max:5120|mimes:png,jpg,jpeg,txt,pdf,doc,docx',
        ]);

        $files = $request->file('files') ?? [];
        if (empty($files)) {
            return response()->json(['status' => 'error', 'message' => 'No file uploaded'], 400);
        }

        $accessToken = session('api_token');
        if (!$accessToken) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized: Missing API token'], 401);
        }

        $apiUrl = 'http://192.168.1.200:5126/Auditor/ExceptionFileUpload';
        $uploadedFiles = [];

        try {
            foreach ($files as $file) {
                // Get the MIME type
                $mimeType = $file->getMimeType(); // Example: "image/jpeg", "application/pdf"
                $base64File = base64_encode(file_get_contents($file->getRealPath()));

                // Append the MIME type header to the base64 data
                $fileData = "data:$mimeType;base64,$base64File";

                $payload = [
                    'exceptionTrackerId' => $id,
                    'fileName' => $file->getClientOriginalName(),
                    'fileData' => $fileData, // Correctly formatted with MIME type
                ];

                $response = Http::withToken($accessToken)->post($apiUrl, $payload);

                if ($response->successful()) {
                    $uploadedFiles[] = [
                        'fileName' => $file->getClientOriginalName(),
                        'uploadDate' => now()->toDateTimeString(),
                        'fileData' => $fileData, // Store file with header
                    ];
                }
            }

            if (!empty($uploadedFiles)) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'All files uploaded successfully',
                    'files' => $uploadedFiles
                ], 200);
            }

            return response()->json(['status' => 'error', 'message' => 'File upload failed'], 500);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Handle connection errors (e.g., API server is down)
            Log::error('Connection Error: Unable to reach Exception file upload API', ['error' => $e->getMessage()]);

            toast('Failed to connect to the server. Please check your internet or try again later.', 'error');
            return [];
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Something went wrong, check your internet and try again'], 500);
        }
    }



    public function downloadExceptionFile($fileId)
    {
        try {
            $accessToken = session('api_token');

            if (!$accessToken) {
                Log::error('Unauthorized: Missing API token for file download');
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized: Missing API token'
                ], 401);
            }

            $apiUrl = "http://192.168.1.200:5126/Auditor/ExceptionFileUpload/{$fileId}";
            $response = Http::withToken($accessToken)->get($apiUrl);

            Log::info('Download API Response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to fetch file'
                ], 500);
            }

            $fileData = $response->json();

            $files = explode(',', $fileData['fileData'], 2);
            $base64Data = $files[1];

            return response()->json([
                'status' => 'success',
                'fileName' => $fileData['fileName'],
                'fileData' => $base64Data, // This includes the file header!
            ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Handle connection errors (e.g., API server is down)
            Log::error('Connection Error: Unable to reach Exception file download API', ['error' => $e->getMessage()]);

            toast('Failed to connect to the server. Please check your internet or try again later.', 'error');
            return [];
        } catch (\Exception $e) {
            Log::error('Error downloading file', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong, please try again'
            ], 500);
        }
    }




    public static function exceptionFileDelete($exceptionId)
    {
        $access_token = session('api_token');

        try {
            // Make the DELETE request to the external API
            $response = Http::withToken($access_token)
                ->delete("http://192.168.1.200:5126/Auditor/ExceptionFileUpload/{$exceptionId}");

            // Check the response status and return appropriate response
            if ($response->successful()) {
                return redirect()->back()->with('toast_success', 'Exception File removed successfully');
            } else {
                // Log the error response
                Log::error('Failed to delete Exception File', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return redirect()->back()->with('toast_error', 'Sorry, failed to delete Exception File');
            }
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            // Handle connection errors (e.g., API server is down)
            Log::error('Connection Error: Unable to reach Exception file delete API', ['error' => $e->getMessage()]);

            toast('Failed to connect to the server. Please check your internet or try again later.', 'error');
            return [];
        } catch (\Exception $e) {
            // Log the exception
            Log::error('Exception occurred while deleting Exception File', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('toast_error', 'Something went wrong, check your internet and try again, <b>Or Contact Application Support</b>');
        }
    }


    public function deleteExceptionFile($fileId)
    {
        try {
            $accessToken = session('api_token');

            if (!$accessToken) {
                Log::error('Unauthorized: Missing API token for file deletion');
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized: Missing API token'
                ], 401);
            }

            $apiUrl = "http://192.168.1.200:5126/Auditor/ExceptionFileUpload/{$fileId}";
            $response = Http::withToken($accessToken)->delete($apiUrl);

            Log::info('Delete API Response', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            if (!$response->successful()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to delete file'
                ], 500);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'File deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting file', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong, please try again'
            ], 500);
        }
    }
}
