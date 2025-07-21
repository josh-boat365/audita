<x-base-layout>
    @php
        // Safely get first exception with null checks
        $firstException = collect($exception)->first() ?? null;
        $batchId = $firstException->exceptionBatchId ?? '';
        $processTypeId = $firstException->processTypeId ?? '';
        $departmentId = $firstException->departmentId ?? '';
        $requestDate = isset($firstException->requestDate)
            ? Carbon\Carbon::parse($firstException->requestDate)->format('Y-m-d')
            : '';
        $employeeName = session('user_name') ?? 'Unknown User';
        $batchStatus = $firstException->status ?? null;

        // Process exceptions with sorting
        $processedExceptions = collect($exception ?? [])->map(function ($batchItem) use ($batchStatus) {
            $exceptions = collect($batchItem->exceptions ?? []);

            // Sort exceptions based on batch status
            if ($batchStatus === 'AMENDMENT') {
                $exceptions = $exceptions
                    ->sortBy(function ($exception) {
                        // Pending goes to bottom, Declined stays in normal order (show Amend button)
                        return ($exception->status ?? null) === 'PENDING' ? 1 : 0;
                    })
                    ->values();
            }
            // For DECLINED status, we keep the original order (all will be declined)

            $batchItem->exceptions = $exceptions->all();
            return $batchItem;
        });

        // Count total exceptions safely
        $totalExceptions = $processedExceptions->sum(fn($item) => count($item->exceptions ?? []));
        $hasExceptions = $totalExceptions > 0;
    @endphp

    <div class="container-fluid px-1">
        <!-- Page Title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">
                        <a href="{{ route('exception.auditor.list') }}">Exceptions</a> >
                        {{ $firstException->submittedBy ?? 'Unknown' }} >
                        {{ $firstException->exceptionBatch ?? 'Unknown Batch' }} >
                        <a href="#">{{ $firstException->departmentName ?? 'Unknown Department' }}</a>
                    </h4>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="d-flex gap-2 mb-4 justify-content-end">
            @if ($batchStatus !== 'DECLINED')
                <button type="button" class="btn btn-primary btn-rounded waves-effect waves-light"
                    id="pushBatchForReview">
                    Push For Review
                </button>
            @endif
        </div>

        <!-- Comments Card -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Comments</h5>
                <p class="card-text">
                    <i>
                        {{ !empty(trim($firstException->statusComment ?? ''))
                            ? $firstException->statusComment
                            : 'No comments on batch, check the exceptions for the needed amendments below if required.' }}
                    </i>
                </p>
            </div>
        </div>

        <!-- Filters -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center g-2">
                            <div class="col-md-3">
                                <label class="form-label">Batch</label>
                                <select class="form-select select2" id="batchFilter">
                                    <option>Select.....</option>
                                    @foreach ($batches as $batch)
                                        <option value="{{ $batch->id }}" @selected($batch->id == $batchId)>
                                            {{ $batch->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Process Types</label>
                                <select class="form-select select2" id="processTypeFilter">
                                    <option>Select.....</option>
                                    @foreach ($processTypes as $processType)
                                        <option value="{{ $processType->id }}" @selected($processType->id == $processTypeId)>
                                            {{ $processType->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Department</label>
                                <select class="form-select select2" id="departmentFilter">
                                    <option>Select.....</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}" @selected($department->id == $departmentId)>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Occurrence Date</label>
                                <input type="date" value="{{ $requestDate }}" class="form-control"
                                    id="occurrenceDateFilter">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 mb-4" style="background-color: gray; height: 1px;"></div>

        <!-- Exceptions Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0" id="exceptionsTable">
                <thead class="table-light">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Exception Title</th>
                        <th scope="col">Exception Description</th>
                        <th scope="col">Sub Process Type</th>
                        <th scope="col">Supervisor Comment</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($hasExceptions)
                        @php $rowNumber = 1; @endphp
                        @foreach ($processedExceptions as $batchItem)
                            @foreach ($batchItem->exceptions ?? [] as $exceptionItem)
                                @php
                                    $exceptionStatus = $exceptionItem->status ?? null;
                                    $isDeclined = $exceptionStatus === 'DECLINED';
                                    $isPending = $exceptionStatus === 'PENDING';

                                    // Determine if Amend button should be shown
                                    $showAmendButton = false;

                                    if ($batchStatus === 'AMENDMENT') {
                                        // Show for DECLINED, hide for PENDING
                                        $showAmendButton = $isDeclined;
                                    }
                                    // For DECLINED batch status, never show Amend button
                                @endphp

                                <tr id="exception-row-{{ $exceptionItem->id }}"
                                    class="{{ $batchStatus === 'DECLINED' ? 'table-danger' : '' }} {{ $isPending ? 'bg-light' : '' }}">
                                    <td>{{ $rowNumber++ }}</td>
                                    <td>{{ $exceptionItem->exceptionTitle ?? 'No Title' }}</td>
                                    <td>{{ $exceptionItem->exception ?? 'No Description' }}</td>
                                    <td>
                                        <select class="form-select sub-process-type" name="subProcessTypeId">
                                            <option value="">Select...</option>
                                            @isset($groupedSubProcessTypes[$batchItem->processTypeId ?? ''])
                                                @foreach ($groupedSubProcessTypes[$batchItem->processTypeId] as $subProcessType)
                                                    <option value="{{ $subProcessType->id }}" @selected($subProcessType->id == ($exceptionItem->subProcessTypeId ?? ''))>
                                                        {{ $subProcessType->name }}
                                                    </option>
                                                @endforeach
                                            @endisset
                                        </select>
                                    </td>
                                    <td>
                                        <span><i>
                                                {{ !empty(trim($exceptionItem->statusComment ?? '')) ? $exceptionItem->statusComment : 'No comment' }}</i></span>
                                    </td>
                                    <td>
                                        @if ($showAmendButton)
                                            <div class="d-flex gap-2">
                                                <button type="button" class="btn btn-dark btn-sm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#amendExceptionModal-{{ $exceptionItem->id }}">
                                                    <i class="bx bx-pencil"></i> Amend
                                                </button>
                                            </div>

                                            <!-- Amend Modal -->
                                            <div class="modal fade" id="amendExceptionModal-{{ $exceptionItem->id }}"
                                                tabindex="-1" aria-labelledby="amendExceptionModalLabel"
                                                aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Amend Exception</h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <form
                                                            action="{{ route('exception.update', $exceptionItem->id) }}"
                                                            method="POST">
                                                            @csrf
                                                            <input type="hidden" name="departmentId"
                                                                value="{{ $batchItem->departmentId ?? '' }}">
                                                            <input type="hidden" name="processTypeId"
                                                                value="{{ $batchItem->processTypeId ?? '' }}">
                                                            <input type="hidden" name="exceptionBatchId"
                                                                value="{{ $batchItem->exceptionBatchId ?? '' }}">
                                                            <input type="hidden" name="occurrenceDate"
                                                                value="{{ isset($exceptionItem->occurrenceDate) ? \Carbon\Carbon::parse($exceptionItem->occurrenceDate)->format('d/m/Y') : '' }}">
                                                            <input type="hidden" name="status" value="PENDING">
                                                            <input type="hidden" name="statusComment"
                                                                value="{{ $exceptionItem->statusComment ?? '' }}">
                                                            <input type="hidden" name="requestType" value="BATCH">
                                                            <input type="hidden" name="requestTrackerId"
                                                                value="{{ $batchItem->id ?? '' }}">

                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Exception Title</label>
                                                                    <textarea class="form-control" name="exceptionTitle" rows="3" required>{{ $exceptionItem->exceptionTitle ?? '' }}</textarea>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Exception
                                                                        Description</label>
                                                                    <textarea class="form-control" name="exception" rows="3" required>{{ $exceptionItem->exception ?? '' }}</textarea>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label">Sub Process Type</label>
                                                                    <select class="form-select sub-process-type"
                                                                        name="subProcessTypeId">
                                                                        <option value="">Select...</option>
                                                                        @isset($groupedSubProcessTypes[$batchItem->processTypeId ?? ''])
                                                                            @foreach ($groupedSubProcessTypes[$batchItem->processTypeId] as $subProcessType)
                                                                                <option value="{{ $subProcessType->id }}"
                                                                                    @selected($subProcessType->id == ($exceptionItem->subProcessTypeId ?? ''))>
                                                                                    {{ $subProcessType->name }}
                                                                                </option>
                                                                            @endforeach
                                                                        @endisset
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary"
                                                                    data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-danger">Save
                                                                    Exception</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    @else
                        <tr>
                            <td colspan="6" class="text-center">All Exceptions Amended. Push <b>Batch for
                                    Review</b></td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Batch Approval
                document.getElementById('pushBatchForReview')?.addEventListener('click', function() {
                    Swal.fire({
                        title: 'Push Batch For Review ?',
                        text: 'Are you sure you want to push this entire batch for review?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Push for Review',
                        cancelButtonText: 'No, Cancel',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            submitBatchAction('PENDING');
                        }
                    });
                });

                function submitBatchAction(status) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route('exception.supervisor.action') }}';

                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

                    if (!csrfToken) {
                        console.error('CSRF token not found');
                        return;
                    }

                    const inputs = [{
                            name: '_token',
                            value: csrfToken
                        },
                        {
                            name: 'batchExceptionId',
                            value: '{{ $firstException->id ?? '' }}'
                        },
                        {
                            name: 'status',
                            value: status
                        }
                    ];

                    inputs.forEach(input => {
                        const el = document.createElement('input');
                        el.type = 'hidden';
                        el.name = input.name;
                        el.value = input.value;
                        form.appendChild(el);
                    });

                    document.body.appendChild(form);
                    form.submit();
                }

                // Process Type Filter Change
                $('#processTypeFilter').change(function() {
                    const processTypeId = $(this).val();
                    if (!processTypeId) return;

                    $.ajax({
                        url: '/get-sub-process-types/' + processTypeId,
                        type: 'GET',
                        success: function(data) {
                            document.querySelectorAll('#exceptionsTable tbody tr').forEach(row => {
                                updateRowSubProcessTypes(row, data);
                            });
                        },
                        error: function(xhr) {
                            console.error('Error loading sub-process types:', xhr.responseText);
                            Swal.fire('Error', 'Failed to load sub-process types', 'error');
                        }
                    });
                });

                function updateRowSubProcessTypes(row, subProcessTypes) {
                    const select = row.querySelector('.sub-process-type');
                    if (!select) return;

                    const currentValue = select.value;
                    select.innerHTML = '<option value="">Select...</option>';

                    subProcessTypes?.forEach(subType => {
                        select.innerHTML += `<option value="${subType.id}">${subType.name}</option>`;
                    });

                    // Restore selection if still valid
                    if (currentValue && subProcessTypes?.some(st => st.id == currentValue)) {
                        select.value = currentValue;
                    }
                }
            });
        </script>
    @endpush
</x-base-layout>
