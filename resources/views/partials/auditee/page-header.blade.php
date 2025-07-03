<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0 font-size-18">
                <a href="{{ route('auditee.exception.list') }}">Exceptions</a> >
                {{ $pendingException->submittedBy ?? '' }} >
                {{ $pendingException->exceptionBatch->activityGroupName ?? '' }} >
                <a href="#">{{ $pendingException->departmentName ?? '' }}</a>
            </h4>
        </div>
    </div>
</div>
