<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\AuditorApiService;
use App\Http\Traits\HandlesApiErrors;

class ChatsAndCommentsController extends Controller
{
    use HandlesApiErrors;

    protected AuditorApiService $apiService;

    public function __construct(AuditorApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function storeComment(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:255',
        ]);

        if (!$this->hasValidApiToken()) {
            return $this->redirectToLoginIfNoToken();
        }

        $data = [
            'exceptionTrackerId' => $id,
            'comment' => $request->input('comment'),
        ];

        try {
            $response = $this->apiService->post(
                $this->apiService->getEndpoint('exception_comment'),
                $data,
                $this->getApiToken()
            );

            return $this->handleApiResponse(
                $response,
                'Comment added successfully',
                'back',
                'Add comment'
            );

        } catch (\Exception $e) {
            return $this->handleApiException($e, 'adding comment', ['exception_id' => $id]);
        }
    }

    public function updateComment(Request $request, $id)
    {
        $request->validate([
            'comment' => 'required|string|max:255',
            'exceptionTrackerId' => 'required|integer',
        ]);

        if (!$this->hasValidApiToken()) {
            return $this->redirectToLoginIfNoToken();
        }

        $data = [
            'id' => $id,
            'exceptionTrackerId' => $request->input('exceptionTrackerId'),
            'comment' => $request->input('comment'),
        ];

        try {
            $response = $this->apiService->put(
                $this->apiService->getEndpoint('exception_comment'),
                $data,
                $this->getApiToken()
            );

            return $this->handleApiResponse(
                $response,
                'Comment updated successfully',
                'back',
                'Update comment'
            );

        } catch (\Exception $e) {
            return $this->handleApiException($e, 'updating comment', ['comment_id' => $id]);
        }
    }

    public function deleteComment($id)
    {
        if (!$this->hasValidApiToken()) {
            return $this->redirectToLoginIfNoToken();
        }

        try {
            $response = $this->apiService->delete(
                "{$this->apiService->getEndpoint('exception_comment')}/{$id}",
                $this->getApiToken()
            );

            return $this->handleApiResponse(
                $response,
                'Exception Comment deleted successfully',
                'back',
                'Delete comment'
            );

        } catch (\Exception $e) {
            return $this->handleApiException($e, 'deleting comment', ['comment_id' => $id]);
        }
    }

    public static function getExceptionComments($exceptionId)
    {
        $service = app(AuditorApiService::class);

        try {
            $response = $service->get(
                $service->getEndpoint('exception_comment'),
                session('api_token')
            );

            if ($response->successful()) {
                $api_response = $response->object() ?? [];
                $comments = collect($api_response)
                    ->filter(fn($comment) => $comment->exceptionTrackerId == $exceptionId)
                    ->all() ?? [];
                return $comments;
            } elseif ($response->status() == 404) {
                Log::warning('Exception comments API returned 404 Not Found');
                toast('Exception comments data not found', 'warning');
                return [];
            } else {
                Log::error('Exception comments API request failed', ['status' => $response->status()]);
                toast('Error fetching exception comments data', 'error');
                return [];
            }
        } catch (\Exception $e) {
            Log::error('Error fetching exception comments', ['error' => $e->getMessage()]);
            toast('An error occurred. Please try again later', 'error');
            return [];
        }
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

        if (!$this->hasValidApiToken()) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized: Missing API token'], 401);
        }

        $uploadedFiles = [];

        try {
            foreach ($files as $file) {
                // Get the MIME type
                $mimeType = $file->getMimeType();
                $base64File = base64_encode(file_get_contents($file->getRealPath()));

                // Append the MIME type header to the base64 data
                $fileData = "data:$mimeType;base64,$base64File";

                $payload = [
                    'exceptionTrackerId' => $id,
                    'fileName' => $file->getClientOriginalName(),
                    'fileData' => $fileData,
                ];

                $response = $this->apiService->post(
                    $this->apiService->getEndpoint('exception_file_upload'),
                    $payload,
                    $this->getApiToken()
                );

                if ($response->successful()) {
                    $uploadedFiles[] = [
                        'fileName' => $file->getClientOriginalName(),
                        'uploadDate' => now()->toDateTimeString(),
                        'fileData' => $fileData,
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

        } catch (\Exception $e) {
            Log::error('Error uploading files', [
                'exception_id' => $id,
                'error' => $e->getMessage()
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong, check your internet and try again'
            ], 500);
        }
    }

    public function downloadExceptionFile($fileId)
    {
        try {
            if (!$this->hasValidApiToken()) {
                Log::error('Unauthorized: Missing API token for file download');
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized: Missing API token'
                ], 401);
            }

            $response = $this->apiService->get(
                "{$this->apiService->getEndpoint('exception_file_upload')}/{$fileId}",
                $this->getApiToken()
            );

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
                'fileData' => $base64Data,
            ]);

        } catch (\Exception $e) {
            Log::error('Error downloading file', [
                'file_id' => $fileId,
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
        $service = app(AuditorApiService::class);

        try {
            $response = $service->delete(
                "{$service->getEndpoint('exception_file_upload')}/{$exceptionId}",
                session('api_token')
            );

            if ($response->successful()) {
                return redirect()->back()->with('toast_success', 'Exception File removed successfully');
            } else {
                Log::error('Failed to delete Exception File', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return redirect()->back()->with('toast_error', 'Sorry, failed to delete Exception File');
            }
        } catch (\Exception $e) {
            Log::error('Exception occurred while deleting Exception File', [
                'exception_id' => $exceptionId,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('toast_error', 'Something went wrong, check your internet and try again, <b>Or Contact Application Support</b>');
        }
    }

    public function deleteExceptionFile($fileId)
    {
        try {
            if (!$this->hasValidApiToken()) {
                Log::error('Unauthorized: Missing API token for file deletion');
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized: Missing API token'
                ], 401);
            }

            $response = $this->apiService->delete(
                "{$this->apiService->getEndpoint('exception_file_upload')}/{$fileId}",
                $this->getApiToken()
            );

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
                'file_id' => $fileId,
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
