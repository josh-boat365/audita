<div class="modal fade" id="confirmSavePushBackModal-{{ $exceptionItem->id }}" tabindex="-1"
    aria-labelledby="confirmSavePushBackModalLabel-{{ $exceptionItem->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            {{-- Modal Header --}}
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="confirmSavePushBackModalLabel-{{ $exceptionItem->id }}">
                    <i class="bx bxs-save me-2"></i>Confirm Response Push Back For Submission
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
                        <p id="responsePushBackPreview-{{ $exceptionItem->id }}">
                            {{ $exceptionItem->statusComment ?? '[No response provided]' }}</p>
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

                <form action="{{ route('auditee.push.back') }}" method="POST"
                    id="pushBackForm-{{ $exceptionItem->id }}">
                    @csrf
                    <input type="hidden" name="exceptionId" value="{{ $exceptionItem->id }}">
                    <input type="hidden" name="requestTrackerId" value="{{ $pendingExceptionBatchStatusId }}">
                    <input type="hidden" name="status" value="APPROVED">
                    <input type="hidden" name="requestType" value="BATCH">
                    {{-- This will be populated by JavaScript --}}
                    <input type="hidden" name="statusComment" id="statusCommentHidden-{{ $exceptionItem->id }}"
                        value="">

                    <button type="submit" class="btn btn-primary">
                        <i class="bx bxs-save me-1"></i>
                        <span class="btn-text">Submit Response</span>
                        <div class="spinner-border spinner-border-sm d-none ms-1" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // When a save modal is shown, update the response preview and hidden input
        $('[id^="confirmSavePushBackModal-"]').on('show.bs.modal', function(event) {
            const exceptionId = $(this).attr('id').replace('confirmSavePushBackModal-', '');

            // Find the corresponding textarea in the table
            const responseTextarea = $(`textarea[name="exceptions[${exceptionId}][response]"]`);
            let responseText = '';

            if (responseTextarea.length > 0) {
                responseText = responseTextarea.val();
            } else {
                // Fallback: try to find any response textarea in the same row
                const row = $(`tr[data-exception-id="${exceptionId}"]`);
                const anyResponseTextarea = row.find('textarea[name*="response"]');
                if (anyResponseTextarea.length > 0) {
                    responseText = anyResponseTextarea.val();
                }
            }

            // Update the preview
            $(`#responsePushBackPreview-${exceptionId}`).text(responseText || '[No response provided]');

            // Update the hidden input
            $(`#statusCommentHidden-${exceptionId}`).val(responseText || '');

            console.log('Modal opening for exception:', exceptionId);
            console.log('Response text found:', responseText);
        });

        // Add form validation before submission
        $('[id^="pushBackForm-"]').on('submit', function(e) {
            const form = $(this);
            const statusComment = form.find('input[name="statusComment"]').val();

            console.log('Form submitting with statusComment:', statusComment);

            if (!statusComment || statusComment.trim() === '') {
                e.preventDefault();
                alert('Please provide a response before submitting');
                return false;
            }

            // Add loading state
            const submitBtn = form.find('button[type="submit"]');
            submitBtn.prop('disabled', true);
            submitBtn.find('.btn-text').text('Submitting...');
            submitBtn.find('.spinner-border').removeClass('d-none');
        });
    });
</script>
