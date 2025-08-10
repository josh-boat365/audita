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
        $exceptions =
            isset($pendingException->exceptions) && is_iterable($pendingException->exceptions)
                ? $pendingException->exceptions
                : [];
    @endphp

    <div class="container-fluid px-1">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18"><a href="{{ route('exception.supervisor.list') }}">Exceptions</a> >
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
                                <select class="form-select select2" id="batchFilter">
                                    <option>Select.....</option>
                                    @foreach ($batches as $batch)
                                        <option value="{{ $batch->id }}" @selected($batch->id === $batchId)>
                                            {{ $batch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Process Types</label>
                                <select class="form-select select2" id="processTypeFilter">
                                    <option>Select.....</option>
                                    @foreach ($processTypes as $processType)
                                        <option value="{{ $processType->id }}" @selected($processType->id === $processTypeId)>
                                            {{ $processType->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Department</label>
                                <select class="form-select select2" id="departmentFilter">
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
                                <input type="date" value="{{ $requestDate }}" class="form-control"
                                    id="occurrenceDateFilter">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 mb-4" style="background-color: gray; height: 1px;"></div>

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

    </div>

</x-base-layout>
