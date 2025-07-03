{{-- partials.auditee.modals --}}

{{-- Check if exceptions exist and iterate through them --}}
@if (isset($exceptions) && is_iterable($exceptions))
    @foreach ($exceptions as $exceptionItem)
        @include('partials.auditee.comments-modal', ['exceptionItem' => $exceptionItem])
        @include('partials.auditee.file-attachments-modal', ['exceptionItem' => $exceptionItem])
        @include('partials.auditee.confirm-save-modal', ['exceptionItem' => $exceptionItem])
        @include('partials.auditee.view-exception-modal', ['exceptionItem' => $exceptionItem])
    @endforeach
@endif

@include('partials.auditee.edit-comment-modal')
@include('partials.auditee.delete-comment-modal')
