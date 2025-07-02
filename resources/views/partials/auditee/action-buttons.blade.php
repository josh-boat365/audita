
{{--  partials.auditee.action-buttons  --}}
<div class="d-flex gap-3 justify-content-center">
    <!-- Comments Button -->
    <button type="button" class="btn btn-sm btn-outline-primary"
            data-bs-toggle="modal" data-bs-target="#commentsModal-{{ $exceptionItem->id }}"
            title="View Comments">
        <i class="bx bx-message-dots"></i>
        <span class="badge rounded-full bg-dark ms-1">{{ count($exceptionItem->comment ?? []) }}</span>
    </button>

    <!-- File Attachments Button -->
    <button type="button" class="btn btn-sm btn-outline-warning"
            data-bs-toggle="modal" data-bs-target="#fileAttachmentsModal-{{ $exceptionItem->id }}"
            title="File Attachments">
        <i class="bx bx-paperclip"></i>
        <span class="badge rounded-full bg-dark ms-1">{{ count($exceptionItem->fileAttached ?? []) }}</span>
    </button>

    <!-- Save Button -->
    @if($pendingExceptionBatchStatus === 'ANALYSIS')
        <button type="button" class="btn btn-sm btn-outline-dark"
                data-bs-toggle="modal" data-bs-target="#viewExceptionModal-{{ $exceptionItem->id }}"
                title="Update Exception">
            <i class="bx bx-pencil"></i>
        </button>

    @else
    <button type="button" class="btn btn-sm btn-outline-success"
            data-bs-toggle="modal" data-bs-target="#confirmSaveModal-{{ $exceptionItem->id }}"
            title="Save Response">
        <i class="bx bxs-save"></i>
    </button>

    @endif
</div>
