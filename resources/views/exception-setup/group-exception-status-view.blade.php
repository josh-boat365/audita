<x-base-layout>
    @php
        //dd($pendingException);
        $pendingException = $pendingException[0] ?? null;
        $batchId = $pendingException->exceptionBatchId ?? '';
        $processTypeId = $pendingException->processTypeId ?? '';
        $departmentId = $pendingException->departmentId ?? '';
        $requestDate = isset($pendingException->requestDate)
            ? Carbon\Carbon::parse($pendingException->requestDate)->format('Y-m-d')
            : '';
        $employeeName = session('user_name') ?? 'Unknown User';

        // Ensure exceptions property exists
        $allowedStatuses = ['RESOLVED', 'NOT-RESOLVED', 'APPROVED'];

        $exceptions =
            isset($pendingException->exceptions) && is_iterable($pendingException->exceptions)
                ? array_filter($pendingException->exceptions, function ($exception) use ($allowedStatuses) {
                    return isset($exception->status) && in_array($exception->status, $allowedStatuses);
                })
                : [];
    @endphp

    <div class="container-fluid px-1">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18"><a href="{{ route('group.exception.status') }}">Exceptions</a> >
                        {{ $pendingException->submittedBy ?? '' }} >
                        {{ $pendingException->exceptionBatch->activityGroupName ?? '' }} > <a
                            href="#">{{ $pendingException->departmentName ?? '' }}</a></h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="mb-3">
            <h1 class="mb-0">View Exception Status</h1>
            <p class="text-muted mb-0">Respond to all exceptions and push them to the auditor for resolution.</p>
        </div>



        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center g-2">
                            <div class="col-md-3">
                                <label class="form-label">Batch</label>
                                <select disabled class="form-select select2" id="batchFilter">
                                    <option>Select.....</option>
                                    @foreach ($batches as $batch)
                                        <option value="{{ $batch->id }}" @selected($batch->id === $batchId)>
                                            {{ $batch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Process Types</label>
                                <select disabled class="form-select select2" id="processTypeFilter">
                                    <option>Select.....</option>
                                    @foreach ($processTypes as $processType)
                                        <option value="{{ $processType->id }}" @selected($processType->id === $processTypeId)>
                                            {{ $processType->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Department</label>
                                <select disabled class="form-select select2" id="departmentFilter">
                                    <option>Select.....</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}" @selected($department->id === $departmentId)>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Occurrence Date</label>
                                <input disabled type="date" value="{{ $requestDate }}" class="form-control"
                                    id="occurrenceDateFilter">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 mb-4" style="background-color: gray; height: 1px;"></div>
        {{--  {{ dd($exceptions) }}  --}}
        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0" id="exceptionsTable">
                <thead class="table-light">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Exception Title</th>
                        <th scope="col">Exception Description</th>
                        <th scope="col">Exception Response</th>
                        <th scope="col">Status</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($exceptions as $key => $exceptionItem)
                        <tr id="exception-row-{{ $exceptionItem->id }}">
                            <td>{{ ++$key }}</td>
                            <td>
                                <textarea disabled class="form-control editable-textarea" rows="3" name="exceptionTitle"
                                    placeholder="Enter exception title">{{ $exceptionItem->exceptionTitle }}</textarea>
                            </td>
                            <td>
                                <textarea disabled class="form-control editable-textarea" rows="3" name="exceptionDescription"
                                    placeholder="Enter exception description">{{ $exceptionItem->exception }}</textarea>
                            </td>
                            <td>
                                <textarea disabled class="form-control editable-textarea" rows="3" name="exceptionDescription"
                                    placeholder="Enter exception description">{{ $exceptionItem->statusComment ?? 'No Comment Yet' }}</textarea>
                            </td>
                            <td>
                                <span
                                    class="badge bg-{{ $exceptionItem->status === 'RESOLVED' ? 'success' : ($exceptionItem->status === 'NOT-RESOLVED' ? 'danger' : 'secondary') }}">
                                    {{ $exceptionItem->status }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-dark" data-bs-toggle="modal"
                                        data-bs-target="#commentsModal-{{ $exceptionItem->id }}" title="View Comments">
                                        <i class="bx bx-message-dots"></i>
                                        <span
                                            class="badge rounded-full bg-dark ms-1">{{ count($exceptionItem->comment ?? []) }}</span>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                        data-bs-target="#viewExceptionModal-{{ $exceptionItem->id }}"
                                        title="Update Exception">
                                        <i class="mdi mdi-eye-outline"></i>
                                    </button>



                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No exceptions found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>


        @foreach ($exceptions as $exceptionItem)
            <div class="modal fade" id="commentsModal-{{ $exceptionItem->id }}" tabindex="-1"
                aria-labelledby="commentsModalLabel-{{ $exceptionItem->id }}" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <!-- Modal Header -->
                        <div class="modal-header">
                            <h5 class="modal-title" id="commentsModalLabel-{{ $exceptionItem->id }}">
                                Comments for Exception #{{ $loop->iteration }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>

                        <!-- Modal Body -->
                        <div class="modal-body p-0">
                            <div class="w-100 user-chat">
                                <div class="card border-0">
                                    <!-- Chat Header -->
                                    <div class="p-4 border-bottom">
                                        <div class="row">
                                            <div class="col-md-4 col-9">
                                                <h5 class="font-size-15 mb-1">{{ $employeeName }}</h5>
                                                <p class="text-muted mb-0">
                                                    <i class="mdi mdi-circle text-success align-middle me-1"></i>
                                                    Active now
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Chat Messages -->
                                    <div class="chat-conversation p-3">
                                        <ul class="list-unstyled mb-0" data-simplebar style="max-height: 400px;">
                                            @include('partials.auditee.comments-list', [
                                                'comments' => $exceptionItem->comment ?? [],
                                                'employeeName' => $employeeName,
                                            ])
                                        </ul>
                                    </div>

                                    <!-- Chat Input -->
                                    @include('partials.auditee.comment-input-form', [
                                        'exceptionItem' => $exceptionItem,
                                    ])
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{--  VIEW EXCEPTION DETAILS MODAL  --}}

            {{--  partials.auditee.view-exception-modal  --}}
            <div class="modal fade" id="viewExceptionModal-{{ $exceptionItem->id }}" tabindex="-1"
                aria-labelledby="viewExceptionModalLabel-{{ $exceptionItem->id }}" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <!-- Modal Header -->
                        <div class="modal-header">
                            <h5 class="modal-title" id="viewExceptionModalLabel-{{ $exceptionItem->id }}">
                                Exception Analysis - Exception #{{ $loop->iteration }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>

                        <!-- Modal Body -->
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-lg-8">
                                    <div class="card border border-primary-subtle">
                                        <div class="card-body">
                                            <h5 class="card-title mb-4">Exception Details</h5>
                                            <!-- Exception Title -->
                                            <div class="mb-4">
                                                <h6 class="text-muted mb-2">Exception Title</h6>
                                                <p class="mb-0">{{ $exceptionItem->exceptionTitle }}</p>
                                            </div>

                                            <!-- Exception Description -->
                                            <div class="mb-4">
                                                <h6 class="text-muted mb-2">Exception Description</h6>
                                                <p class="mb-0">{{ $exceptionItem->exception }}</p>
                                            </div>

                                            <!-- Root Cause -->
                                            <div class="mb-4">
                                                <h6 class="text-muted mb-2">Root Cause</h6>
                                                <p class="mb-0">{{ $exceptionItem->rootCause ?: 'Not specified' }}
                                                </p>
                                            </div>

                                            <!-- Branch/Auditee Response -->
                                            <div class="mb-4">
                                                <h6 class="text-muted mb-2">Branch/Auditee Response</h6>
                                                <p class="mb-0">
                                                    {{ $exceptionItem->statusComment ?: 'No response provided' }}</p>
                                            </div>

                                            <!-- Department and Occurrence Date Row -->
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-4">
                                                        <h6 class="text-muted mb-2">Unit/Department</h6>
                                                        <p class="mb-0">{{ $exceptionItem->department }}</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-4">
                                                        <h6 class="text-muted mb-2">Occurrence Date</h6>
                                                        <p class="mb-0">
                                                            {{ $exceptionItem->occurrenceDate ? \Carbon\Carbon::parse($exceptionItem->occurrenceDate)->format('M d, Y') : 'Not specified' }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-4">
                                    <!-- Status Card -->
                                    <div class="card">
                                        <div class="card-body border border-primary-subtle">
                                            <h5 class="card-title mb-4">Exception Statuses</h5>

                                            <!-- Status -->
                                            <div class="mb-3">
                                                <h6 class="text-muted mb-2">Status</h6>
                                                <span
                                                    class="badge
                                                        @if ($exceptionItem->status === 'APPROVED') bg-primary
                                                        @elseif($exceptionItem->status === 'RESOLVED') bg-success
                                                        @elseif($exceptionItem->status === 'NOT-RESOLVED') bg-warning
                                                        @else bg-secondary @endif fs-6">
                                                    {{ $exceptionItem->status ?: 'Pending' }}
                                                </span>
                                            </div>

                                            <!-- Risk Rate -->
                                            <div class="mb-3">
                                                <h6 class="text-muted mb-2">Risk Rate</h6>
                                                <p class="mb-0">
                                                    @if ($exceptionItem->riskRate)
                                                        {{ $exceptionItem->riskRate }}
                                                    @else
                                                        Not specified
                                                    @endif
                                                </p>
                                            </div>

                                            <!-- Batch -->
                                            <div class="mb-3">
                                                <h6 class="text-muted mb-2">Batch</h6>
                                                <p class="mb-0">
                                                    @if ($exceptionItem->exceptionBatch)
                                                        {{ $exceptionItem->exceptionBatch ?? 'Not specified' }}
                                                    @else
                                                        Not specified
                                                    @endif
                                                </p>
                                            </div>

                                            <!-- Process Type -->
                                            <div class="mb-3">
                                                <h6 class="text-muted mb-2">Process Type/Scope</h6>
                                                <p class="mb-0">
                                                    @if ($exceptionItem->processType)
                                                        {{ $exceptionItem->processType }}
                                                    @else
                                                        Not specified
                                                    @endif
                                                </p>
                                            </div>

                                            <!-- Sub Process Type -->
                                            <div class="mb-3">
                                                <h6 class="text-muted mb-2">Sub Process Type/Scope</h6>
                                                <p class="mb-0">
                                                    @if ($exceptionItem->subProcessType)
                                                        {{ $exceptionItem->subProcessType }}
                                                    @else
                                                        Not specified
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Dates Card -->
                                    <div class="card">
                                        <div class="card-body border border-primary-subtle">
                                            <h6 class="card-title mb-3">Important Dates</h6>

                                            <!-- Proposed Resolution Date -->
                                            <div class="mb-3">
                                                <h6 class="text-muted mb-2">Proposed Resolution Date</h6>
                                                <p class="mb-0">
                                                    @if ($exceptionItem->proposeResolutionDate)
                                                        {{ \Carbon\Carbon::parse($exceptionItem->proposeResolutionDate)->format('M d, Y') }}
                                                    @else
                                                        <span class="text-muted">Not set</span>
                                                    @endif
                                                </p>
                                            </div>

                                            <!-- Resolution Date -->
                                            <div class="mb-3">
                                                <h6 class="text-muted mb-2">Resolution Date</h6>
                                                <p class="mb-0">
                                                    @if ($exceptionItem->resolutionDate)
                                                        {{ \Carbon\Carbon::parse($exceptionItem->resolutionDate)->format('M d, Y') }}
                                                    @else
                                                        <span class="text-muted">Not resolved</span>
                                                    @endif
                                                </p>
                                            </div>

                                            @if ($exceptionItem->createdAt)
                                                <!-- Created Date -->
                                                <div class="mb-0">
                                                    <h6 class="text-muted mb-2">Created</h6>
                                                    <p class="mb-0">
                                                        {{ \Carbon\Carbon::parse($exceptionItem->createdAt)->format('M d, Y \a\t g:i A') }}
                                                    </p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach

    </div>

</x-base-layout>
