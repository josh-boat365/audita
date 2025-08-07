{{-- exception-setup.auditee-exception-view --}}
<x-base-layout>
    {{-- PHP Variables Setup with null checks --}}
    @php
        $pendingException = $exception;
        $batchId = $pendingException->exceptionBatchId ?? '';
        $processTypeId = $pendingException->processTypeId ?? '';
        $departmentId = $pendingException->departmentId ?? '';
        $requestDate = isset($pendingException->requestDate)
            ? Carbon\Carbon::parse($pendingException->requestDate)->format('Y-m-d')
            : '';
        $employeeName = session('user_name') ?? 'Unknown User';

        // Ensure exceptions property exists
        $exceptions =
            isset($pendingException->exceptions) && is_iterable($pendingException->exceptions)
                ? $pendingException->exceptions
                : [];
    @endphp

    {{--  @php
        use Illuminate\Support\Str;
    @endphp  --}}

    <div class="container-fluid px-1">
        {{-- ========== PAGE HEADER SECTION ========== --}}
        @include('partials.auditee.page-header')

        <div class="mb-3">
            {{-- Page Title --}}
            @if (Str::contains(request()->url(), 'APPROVED'))
                <h1 class="mb-0">Exception Response View</h1>
                <p class="text-muted mb-0">Respond to all exceptions and push them to the auditor for resolution.</p>
            @else
                <h1 class="mb-0">Exception Analysis View</h1>
                <p class="text-muted mb-0">Perform your final analysis on these exceptions and push for completion.</p>
            @endif
        </div>


        {{-- ========== FILTER SECTION ========== --}}
        @if (!empty($pendingException) && count($exceptions) > 0)
            @include('partials.auditee.filters', [
                'pendingExceptionBatchStatus' => $pendingException->status,
            ])
        @else
            <div class="alert alert-info">
                Batch filters are not available.
            </div>
        @endif

        {{-- ========== VISUAL SEPARATOR ========== --}}
        <div class="mt-4 mb-4" style="background-color: gray; height: 1px;"></div>

        {{--  <h1>{{ dd($pendingException) }}</h1>  --}}

        {{-- ========== EXCEPTIONS TABLE SECTION ========== --}}
        @if (!empty($pendingException) && count($exceptions) > 0)
            @include('partials.auditee.exceptions-table', [
                'pendingException' => $pendingException,
                'exceptions' => $exceptions,
                'groupedSubProcessTypes' => $groupedSubProcessTypes ?? [],
            ])
        @else
            <div class="alert alert-info">
                No exceptions found for this batch.
            </div>
        @endif

        {{-- ========== MODALS SECTION ========== --}}
        @include('partials.auditee.modals')
    </div>

    {{-- ========== JAVASCRIPT SECTION ========== --}}
    @include('partials.auditee.scripts')
</x-base-layout>
