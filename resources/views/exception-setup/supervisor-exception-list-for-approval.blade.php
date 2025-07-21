<x-base-layout>
    @php
        $pendingException = $exception;
        $batchId = $pendingException->exceptionBatchId ?? '';
        $processTypeId = $pendingException->processTypeId ?? '';
        $departmentId = $pendingException->departmentId ?? '';
        $requestDate = isset($pendingException->requestDate)
            ? Carbon\Carbon::parse($pendingException->requestDate)->format('Y-m-d')
            : '';
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


        <div class="d-flex gap-2 mb-4 justify-content-end">
            <!-- Edit Batch Button -->
            <button type="button" class="btn btn-dark btn-rounded waves-effect waves-light" id="amendBatchBtn">
                Amend Batch
            </button>

            <!-- Approve Batch Button -->
            <button type="button" class="btn btn-primary btn-rounded waves-effect waves-light" id="approveBatchBtn">
                Approve Batch
            </button>

            <!-- Decline Batch Button -->
            <button type="button" class="btn btn-danger btn-rounded waves-effect waves-light" id="declineBatchBtn"
                data-bs-toggle="modal" data-bs-target="#declineBatchModal">
                Decline Batch
            </button>
        </div>

        <!-- Edit Batch Modal -->
        {{--
        <div class="modal fade" tabindex="-1" aria-labelledby="editBatchModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editBatchModalLabel">Edit Batch Request</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="editBatchForm" method="POST"
                        action="{{ route('exception.supervisor.approve-decline') }}">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="editComment" class="form-label">Edit Comments</label>
                                <textarea class="form-control" name="statusComment" id="editComment" rows="3"
                                    placeholder="Explain why this batch needs editing..." required></textarea>
                            </div>
                            <input type="hidden" name="batchExceptionId" value="{{ $pendingException->id ?? '' }}">
                            <input type="hidden" name="status" value="AMENDMENT">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-dark">Submit Edit Request</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>  --}}

        <!-- Decline Batch Modal -->
        <div class="modal fade" id="declineBatchModal" tabindex="-1" aria-labelledby="declineBatchModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="declineBatchModalLabel">Decline Batch</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="declineBatchForm" method="POST" action="{{ route('exception.supervisor.action') }}">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="declineComment" class="form-label">Reason for Decline</label>
                                <textarea class="form-control" name="statusComment" id="declineComment" rows="3"
                                    placeholder="Explain why you're declining this batch..." required></textarea>
                            </div>
                            <input type="hidden" name="batchExceptionId" value="{{ $pendingException->id ?? '' }}">
                            <input type="hidden" name="status" value="DECLINED">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Confirm Decline</button>
                        </div>
                    </form>
                </div>
            </div>
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
                        <th scope="col">Sub Process Type</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pendingException->exceptions as $key => $exceptionItem)
                        <tr id="exception-row-{{ $exceptionItem->id }}">
                            <td>{{ ++$key }}</td>
                            <td>
                                <textarea class="form-control editable-textarea" rows="3" name="exceptionTitle"
                                    placeholder="Enter exception title">{{ $exceptionItem->exceptionTitle }}</textarea>
                            </td>
                            <td>
                                <textarea class="form-control editable-textarea" rows="3" name="exceptionDescription"
                                    placeholder="Enter exception description">{{ $exceptionItem->exception }}</textarea>
                            </td>
                            <td>
                                <select class="form-select sub-process-type" name="subProcessTypeId">
                                    <option value="">Select...</option>
                                    @if (isset($groupedSubProcessTypes[$pendingException->processTypeId]))
                                        @foreach ($groupedSubProcessTypes[$pendingException->processTypeId] as $subProcessType)
                                            <option value="{{ $subProcessType->id }}" @selected($subProcessType->id === $exceptionItem->subProcessTypeId)>
                                                {{ $subProcessType->name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    {{--  <button type="button" class="btn btn-secondary btn-sm push-for-amendment-btn"
                                        data-exception-id="{{ $exceptionItem->id }}">
                                        <i class="bx bx-check"></i> Push for Edit
                                    </button>  --}}
                                    <button type="button" class="btn btn-danger btn-sm decline-btn"
                                        data-exception-id="{{ $exceptionItem->id }}" data-bs-toggle="modal"
                                        data-bs-target="#declineModal">
                                        <i class="bx bx-x"></i> Decline
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

        <!-- Decline Modal -->
        <div class="modal fade" id="declineModal" tabindex="-1" aria-labelledby="declineModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="declineModalLabel">Decline Exception</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="statusComment" class="form-label">Reason for Decline</label>
                            <textarea class="form-control" name="statusComment" id="statusComment" rows="3"
                                placeholder="Enter reason for decline..." required></textarea>
                        </div>
                        <input type="hidden" id="currentExceptionId">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" id="confirmDeclineBtn" class="btn btn-danger">Confirm Decline</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="{{ asset('assets/js/ajax.jquery.min.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Function to load sub-process types for a process type
                function loadSubProcessTypes(processTypeId, callback) {
                    if (!processTypeId) return;

                    $.ajax({
                        url: '/get-sub-process-types/' + processTypeId,
                        type: 'GET',
                        success: function(data) {
                            if (callback) callback(data);
                        },
                        error: function(xhr) {
                            console.error('Error loading sub-process types:', xhr.responseText);
                            Swal.fire('Error', 'Failed to load sub-process types', 'error');
                        }
                    });
                }

                // Update a row's sub-process dropdown
                function updateRowSubProcessTypes(row, subProcessTypes) {
                    const select = row.querySelector('.sub-process-type');
                    const currentValue = select.value;

                    select.innerHTML = '<option value="">Select...</option>';
                    subProcessTypes.forEach(subType => {
                        select.innerHTML += `<option value="${subType.id}">${subType.name}</option>`;
                    });

                    // Restore selection if still valid
                    if (currentValue && subProcessTypes.some(st => st.id == currentValue)) {
                        select.value = currentValue;
                    }
                }

                // When process type filter changes
                $('#processTypeFilter').change(function() {
                    const processTypeId = $(this).val();

                    if (!processTypeId) return;

                    loadSubProcessTypes(processTypeId, function(subProcessTypes) {
                        // Update all existing rows
                        document.querySelectorAll('#exceptionsTable tbody tr').forEach(row => {
                            updateRowSubProcessTypes(row, subProcessTypes);
                        });
                    });
                });

                // SUB-EXCPETION STATUSES -  individual rows
                document.querySelectorAll('.push-for-amendment-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const exceptionId = this.getAttribute('data-exception-id');

                        Swal.fire({
                            title: 'Confirm To Push Exception for Amendment',
                            text: 'Are you sure you want to push this exception for amendment?',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, push for amendment',
                            cancelButtonText: 'Cancel'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                submitExceptionAction(exceptionId, 'AMENDMENT');
                            }
                        });
                    });
                });

                // Decline button handler for individual rows
                document.querySelectorAll('.decline-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const exceptionId = this.getAttribute('data-exception-id');
                        document.getElementById('currentExceptionId').value = exceptionId;
                        document.getElementById('statusComment').value = ''; // Clear previous reason
                    });
                });

                // Confirm decline button in modal
                document.getElementById('confirmDeclineBtn').addEventListener('click', function() {
                    const reason = document.getElementById('statusComment').value.trim();
                    const exceptionId = document.getElementById('currentExceptionId').value;

                    if (!reason) {
                        Swal.fire('Error', 'Please provide a reason for decline', 'error');
                        return;
                    }

                    submitExceptionAction(exceptionId, 'DECLINED', reason);

                    // Close modal
                    bootstrap.Modal.getInstance(document.getElementById('declineModal')).hide();
                });


                // SUBMIT BATCH APPROVAL ACTION - PARENT BATCH STATUS
                document.getElementById('approveBatchBtn').addEventListener('click', function() {
                    Swal.fire({
                        title: 'Approve Batch?',
                        text: 'Are you sure you want to approve this entire batch?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Approve',
                        cancelButtonText: 'No, Cancel',
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            submitBatchAction('APPROVED', 'Batch approved');
                        }
                    });
                });


                // Form validation for edit batch
                document.getElementById('amendBatchBtn').addEventListener('click', function(e) {
                    Swal.fire({
                        title: 'Confirm To Push Batch for Amendment',
                        text: 'Are you sure you want to push this batch for amendment?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, push for amendment',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            submitBatchAction('AMENDMENT', 'Batch pushed for amendment');
                        }
                    });
                });

                // Form validation for decline batch
                document.getElementById('declineBatchForm')?.addEventListener('submit', function(e) {
                    const comment = document.getElementById('declineComment')?.value.trim();
                    if (!comment) {
                        e.preventDefault();
                        alert('Please provide a reason for declining');
                        document.getElementById('declineComment')?.focus();
                    } else {
                        // Auto-submit if comment is valid
                        submitBatchAction('DECLINED', comment);
                    }
                });

                function submitBatchAction(status, comment) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route('exception.supervisor.action') }}';

                    const csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value = document.querySelector('meta[name="csrf-token"]').content;

                    const batchId = document.createElement('input');
                    batchId.type = 'hidden';
                    batchId.name = 'batchExceptionId';
                    batchId.value = '{{ $pendingException->id ?? '' }}';

                    const statusInput = document.createElement('input');
                    statusInput.type = 'hidden';
                    statusInput.name = 'status';
                    statusInput.value = status;

                    const commentInput = document.createElement('input');
                    commentInput.type = 'hidden';
                    commentInput.name = 'statusComment';
                    commentInput.value = comment;

                    form.appendChild(csrf);
                    form.appendChild(batchId);
                    form.appendChild(statusInput);
                    form.appendChild(commentInput);
                    document.body.appendChild(form);
                    form.submit();
                }


                // SUBMIT EXCEPTION ACTION FOR INDIVIDUAL EXCEPTIONS
                function submitExceptionAction(exceptionId, action, declineReason = null) {
                    const row = $('#exception-row-' + exceptionId);
                    const formData = {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        singleExceptionId: exceptionId,
                        status: action,
                        batchExceptionId: '{{ $pendingException->id ?? '' }}',
                        statusComment: declineReason
                    };

                    // Show loading indicator
                    const buttons = row.find('.push-for-amendment-btn, .decline-btn');
                    buttons.prop('disabled', true).html('<i class="bx bx-loader bx-spin"></i> Processing');

                    $.ajax({
                        url: '{{ route('exception.supervisor.approve-decline') }}',
                        type: 'POST',
                        data: formData,
                        dataType: 'json',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        success: function(response) {
                            if (response.success) {
                                // Remove the processed row
                                row.fadeOut(300, function() {
                                    $(this).remove();

                                    // Update UI if no rows left
                                    if ($('#exceptionsTable tbody tr').length === 0) {
                                        $('#exceptionsTable tbody').html(
                                            '<tr><td colspan="5" class="text-center">No exceptions remaining</td></tr>'
                                        );
                                    }
                                });

                                // Show success toast
                                const Toast = Swal.mixin({
                                    toast: true,
                                    position: 'top-end',
                                    showConfirmButton: false,
                                    timer: 3000
                                });

                                Toast.fire({
                                    icon: 'success',
                                    title: response.message
                                });
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            let errorMessage = 'An error occurred while processing your request';

                            try {
                                const response = xhr.responseJSON;
                                if (response && response.message) {
                                    errorMessage = response.message;
                                } else if (xhr.responseText) {
                                    errorMessage = xhr.responseText.substring(0, 200);
                                }
                            } catch (e) {
                                console.error('Error parsing error response:', e);
                            }

                            Swal.fire('Error', errorMessage, 'error');
                        },
                        complete: function() {
                            buttons.prop('disabled', false)
                                .html(function() {
                                    return $(this).hasClass('push-for-amendment-btn') ?
                                        '<i class="bx bx-check"></i> Push for Edit' :
                                        '<i class="bx bx-x"></i> Decline';
                                });
                        }
                    });
                }

                // Auto-resize textareas
                document.querySelectorAll('.editable-textarea').forEach(textarea => {
                    textarea.addEventListener('input', function() {
                        this.style.height = 'auto';
                        this.style.height = (this.scrollHeight) + 'px';
                    });
                    // Trigger initial resize
                    textarea.dispatchEvent(new Event('input'));
                });
            });
        </script>

        <style>
            .editable-textarea {
                resize: none;
                overflow: hidden;
                min-height: 60px;
            }

            .sub-process-type {
                min-width: 150px;
            }
        </style>
    @endpush
</x-base-layout>
