<div class="modal fade" id="confirmSaveModal-{{ $exceptionItem->id }}" tabindex="-1"
    aria-labelledby="confirmSaveModalLabel-{{ $exceptionItem->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            {{-- Modal Header --}}
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="confirmSaveModalLabel-{{ $exceptionItem->id }}">
                    <i class="bx bxs-save me-2"></i>Confirm Response Submission
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            {{-- Modal Body --}}
            <div class="modal-body">
                <div class="mb-4">
                    <h6 class="text-muted mb-3">Exception Details</h6>
                    <div class="card bg-light p-3 mb-3">
                        <p class="mb-1"><strong>Title:</strong> {{ $exceptionItem->exceptionTitle }}</p>
                        <p class="mb-0"><strong>Description:</strong> {{ $exceptionItem->exception }}</p>
                    </div>

                    <h6 class="text-muted mb-3">Your Response</h6>
                    <div class="card bg-light p-3 mb-3">
                        <p id="responsePreview-{{ $exceptionItem->id }}">
                            {{ $exceptionItem->response ?? '[No response provided]' }}</p>
                    </div>

                    <div class="alert alert-warning mt-3">
                        <i class="bx bx-info-circle me-2"></i> Please review your response before submitting.
                    </div>
                </div>
            </div>

            {{-- Modal Footer --}}
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bx bx-x me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary save-response-btn"
                    data-exception-id="{{ $exceptionItem->id }}" data-exception-item-id="{{ $exceptionItem->id }}">
                    <i class="bx bxs-save me-1"></i>
                    <span class="btn-text">Submit Response</span>
                    <div class="spinner-border spinner-border-sm d-none ms-1" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // When a save modal is shown, update the response preview
            $('[id^="confirmSaveModal-"]').on('show.bs.modal', function(event) {
                const exceptionId = $(this).attr('id').replace('confirmSaveModal-', '');
                const responseText = $(`textarea[name="exceptions[${exceptionId}][response]"]`).val();
                $(`#responsePreview-${exceptionId}`).text(responseText || '[No response provided]');
            });

            // Handle save button click
            $(document).on('click', '.save-response-btn', function() {
                const $btn = $(this);
                const exceptionId = $btn.data('exception-id');
                const exceptionItemId = $btn.data('exception-item-id');
                const responseText = $(`textarea[name="exceptions[${exceptionId}][response]"]`).val();

                // Validate response text
                if (!responseText || responseText.trim() === '') {
                    showErrorToast('Please provide a response before submitting');
                    return;
                }

                // Show loading state
                $btn.prop('disabled', true);
                $btn.find('.btn-text').text('Submitting...');
                $btn.find('.spinner-border').removeClass('d-none');

                // Prepare data for submission
                const formData = {
                    exceptionItemId: exceptionItemId,
                    status: 'RESOLVED',
                    statusComment: responseText.trim()
                };

                $.ajax({
                    url: '/exception/auditee-response',
                    method: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#confirmSaveModal-' + exceptionId).modal('hide');
                            showSuccessToast(response.message ||
                                'Response submitted successfully');

                            // Optional: Update UI elements to reflect the change
                            // You can add code here to update the status in the table/list
                            // For example:
                            // $(`#exception-${exceptionId}`).find('.status').text('RESOLVED').addClass('badge-success');

                            // Optional: Refresh part of the page or redirect
                            location.reload(); // Uncomment if you want to refresh the page
                        } else {
                            showErrorToast(response.message ||
                                'An error occurred while submitting the response');
                        }
                    },
                    error: function(xhr, status, error) {
                        let errorMessage = 'An error occurred while submitting the response';

                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.message) {
                                errorMessage = response.message;
                            }

                            // Handle validation errors
                            if (response.errors && typeof response.errors === 'object') {
                                const errorMessages = Object.values(response.errors).flat();
                                errorMessage = errorMessages.join(', ');
                            }
                        } catch (e) {
                            // If response is not JSON, use status text or default message
                            if (xhr.status === 401) {
                                errorMessage = 'Session expired. Please login again.';
                                // Optional: Redirect to login page
                                // window.location.href = '/login';
                            } else if (xhr.status === 422) {
                                errorMessage = 'Please check your input and try again.';
                            } else if (xhr.status >= 500) {
                                errorMessage = 'Server error. Please try again later.';
                            }
                        }

                        showErrorToast(errorMessage);
                        console.error('Error submitting response:', xhr.responseText);
                    },
                    complete: function() {
                        // Reset button state
                        $btn.prop('disabled', false);
                        $btn.find('.btn-text').text('Submit Response');
                        $btn.find('.spinner-border').addClass('d-none');
                    }
                });
            });
        });

        // Toast notification functions - make sure these exist in your layout or add them
        function showSuccessToast(message) {
            // Assuming you have a toast system in place
            // Adjust this based on your toast implementation
            if (typeof toastr !== 'undefined') {
                toastr.success(message);
            } else if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: message,
                    timer: 3000,
                    showConfirmButton: false
                });
            } else {
                alert('Success: ' + message);
            }
        }

        function showErrorToast(message) {
            // Assuming you have a toast system in place
            // Adjust this based on your toast implementation
            if (typeof toastr !== 'undefined') {
                toastr.error(message);
            } else if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: message
                });
            } else {
                alert('Error: ' + message);
            }
        }
    </script>
@endpush
