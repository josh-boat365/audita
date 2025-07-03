<div class="modal fade" id="fileAttachmentsModal-{{ $exceptionItem->id }}" tabindex="-1"
    aria-labelledby="fileAttachmentsModalLabel-{{ $exceptionItem->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="fileAttachmentsModalLabel-{{ $exceptionItem->id }}">
                    File Attachments - Exception #{{ $loop->iteration }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <!-- Existing Files Section -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">
                            <i class="bx bx-file me-2"></i>Attached Files
                            <span class="badge bg-secondary ms-2">
                                {{ count($exceptionItem->fileAttached ?? []) }}
                            </span>
                        </h6>
                    </div>

                    <div class="row border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                        @include('partials.auditee.attachment-list', [
                            'attachments' => $exceptionItem->fileAttached ?? [],
                            'exceptionItem' => $exceptionItem,
                        ])
                    </div>
                </div>

                <!-- File Upload Section -->
                @include('partials.auditee.file-upload-form', ['exceptionItem' => $exceptionItem])
            </div>
        </div>
    </div>
</div>
