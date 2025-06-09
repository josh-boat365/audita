<x-base-layout>
    <div class="container-fluid px-1">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Bulk Exception Create</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="d-flex gap-2 mb-4 justify-content-end">
            <button data-bs-toggle="modal" data-bs-target="#addSubProcessTypeModal"
                class="btn btn-primary btn-rounded waves-effect waves-light">
                <i class="bx bx-plus"></i> Add subProcessType
            </button>
            <button id="addRowBtn" class="btn btn-success btn-rounded waves-effect waves-light">
                <i class="bx bx-plus"></i> Add Row
            </button>
        </div>

        {{--  modal for adding subProcessType  --}}
        <div class="modal fade" id="addSubProcessTypeModal" tabindex="-1" role="dialog"
            aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-top">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addSubProcessTypeModalLabel">Add Sub Process Type</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('sub.process.type') }}" method="POST" id="addSubProcessTypeForm">
                            @csrf
                            <div class="mb-3">
                                <label for="subProcessTypeName" class="form-label">Sub Process Type Name</label>
                                <input type="text" class="form-control" id="subProcessTypeName" name="name"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label for="processTypeSelect" class="form-label">Process Type</label>
                                <select class="form-select" id="processTypeSelect" name="processTypeId" required>
                                    <option> Select ......</option>
                                    @foreach ($processTypes as $processType)
                                        <option value="{{ $processType->id }}">{{ $processType->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <input type="hidden" name="active" value="1">
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Add Sub Process Type</button>
                            </div>
                        </form>
                    </div>
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
                                        <option value="{{ $batch->id }}">{{ $batch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Process Type</label>
                                <select class="form-select select2" id="processTypeFilter">
                                    <option>Select.....</option>
                                    @foreach ($processTypes as $processType)
                                        <option value="{{ $processType->id }}">{{ $processType->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Department</label>
                                <select class="form-select select2" id="departmentFilter">
                                    <option>Select.....</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Occurrence Date</label>
                                <input type="date" class="form-control" id="occurrenceDateFilter">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 mb-4" style="background-color: gray; height: 1px;"></div>

        <form id="bulkExceptionForm" action="{{ route('bulk.exception.create') }}" method="POST">
            @csrf

            <input type="hidden" name="processTypeId" id="formProcessTypeId">
            <input type="hidden" name="departmentId" id="formDepartmentId">
            <input type="hidden" name="exceptionBatchId" id="formBatchId">
            <input type="hidden" name="occurrenceDate" id="formOccurrenceDate">

            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0" id="exceptionsTable">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Exception Title</th>
                            <th scope="col">Exception Description</th>
                            <th scope="col">Sub Category</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Rows will be added dynamically here -->
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-3 mb-5">
                <button type="submit" class="btn btn-primary btn-rounded waves-effect waves-light">
                    <i class="bx bx-save"></i> Submit All Exceptions
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="{{ asset('assets/js/ajax.jquery.min.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                let rowCount = 0;
                const tableBody = document.querySelector('#exceptionsTable tbody');
                const form = document.getElementById('bulkExceptionForm');
                const addRowBtn = document.getElementById('addRowBtn');
                const requiredFilters = ['#batchFilter', '#processTypeFilter', '#departmentFilter',
                    '#occurrenceDateFilter'
                ];

                // Store the grouped sub-process types from PHP
                {{--  const groupedSubProcessTypes = @json($groupedSubProcessTypes);  --}}

                // Initially disable the add row button
                addRowBtn.disabled = true;

                // Function to validate filters
                function validateFilters() {
                    let isValid = true;

                    requiredFilters.forEach(filter => {
                        const element = document.querySelector(filter);
                        const parent = element.closest('.col-md-3');

                        if (!element.value || element.value === 'Select.....') {
                            parent.classList.add('has-error');
                            isValid = false;
                        } else {
                            parent.classList.remove('has-error');
                        }
                    });

                    // Enable/disable add row button based on validation
                    addRowBtn.disabled = !isValid;

                    return isValid;
                }

                // Function to get sub-process types for a given process type
                {{--  function getSubProcessTypes(processTypeId) {
                    return groupedSubProcessTypes[processTypeId] || [];
                }  --}}

                // Function to show validation error
                function showValidationError() {
                    const missingFields = [];
                    requiredFilters.forEach(filter => {
                        const element = document.querySelector(filter);
                        if (!element.value || element.value === 'Select.....') {
                            const label = element.closest('.col-md-3').querySelector('label').textContent;
                            missingFields.push(label);
                        }
                    });

                    if (missingFields.length > 0) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Missing Required Fields',
                            html: `Please select: <strong>${missingFields.join(', ')}</strong> before adding a row.`,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 5000,
                            timerProgressBar: true,
                        });
                    }
                }

                // Real-time validation on filter changes
                requiredFilters.forEach(filter => {
                    document.querySelector(filter).addEventListener('change', function() {
                        validateFilters();
                        // Remove error styling when user makes a selection
                        this.closest('.col-md-3').classList.remove('has-error');
                    });
                });


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
                    document.getElementById('formProcessTypeId').value = processTypeId;

                    if (!processTypeId) return;

                    loadSubProcessTypes(processTypeId, function(subProcessTypes) {
                        // Update all existing rows
                        document.querySelectorAll('#exceptionsTable tbody tr').forEach(row => {
                            updateRowSubProcessTypes(row, subProcessTypes);
                        });
                    });
                });

                // Add new row with validation
                addRowBtn.addEventListener('click', function() {
                    if (!validateFilters()) {
                        showValidationError();
                        return;
                    }

                    const processTypeId = document.getElementById('processTypeFilter').value;

                    loadSubProcessTypes(processTypeId, function(subProcessTypes) {
                        rowCount++;
                        const newRow = document.createElement('tr');
                        newRow.dataset.rowId = rowCount;
                        newRow.innerHTML = `
                    <td>${rowCount}</td>
                    <td>
                        <textarea class="form-control editable-textarea"
                                  rows="3"
                                  name="exceptions[${rowCount}][exceptionTitle]"
                                  placeholder="Enter exception title" required></textarea>
                    </td>
                    <td>
                        <textarea class="form-control editable-textarea"
                                  rows="3"
                                  name="exceptions[${rowCount}][exception]"
                                  placeholder="Enter exception description" required></textarea>
                    </td>
                    <td>
                        <select class="form-select sub-process-type"
                                name="exceptions[${rowCount}][subProcessTypeId]"
                                required>
                            <option value="">Loading...</option>
                        </select>
                    </td>
                    <td>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-danger delete-row">
                                <i class="bx bxs-trash"></i> Delete
                            </button>
                        </div>
                    </td>
                `;

                        tableBody.appendChild(newRow);
                        updateRowSubProcessTypes(newRow, subProcessTypes);
                        newRow.querySelector('textarea').focus();

                        // Update hidden form fields
                        document.getElementById('formProcessTypeId').value = processTypeId;
                        document.getElementById('formDepartmentId').value = document.getElementById(
                            'departmentFilter').value;
                        document.getElementById('formBatchId').value = document.getElementById(
                            'batchFilter').value;
                        document.getElementById('formOccurrenceDate').value = document.getElementById(
                            'occurrenceDateFilter').value;
                    });
                });

                // When process type filter changes, update all existing rows' sub-process type dropdowns
                {{--  document.getElementById('processTypeFilter').addEventListener('change', function() {
                    const processTypeId = this.value;
                    const subProcessTypes = getSubProcessTypes(processTypeId);

                    document.querySelectorAll('.sub-process-type').forEach(select => {
                        // Only update selects that belong to this process type or haven't been assigned yet
                        if (!select.dataset.processType || select.dataset.processType ===
                            processTypeId) {
                            select.innerHTML = `
                        <option value="">Select...</option>
                        ${subProcessTypes.map(subType =>
                            `<option value="${subType.id}">${subType.name}</option>`
                        ).join('')}
                    `;
                            select.dataset.processType = processTypeId;
                        }
                    });
                });  --}}

                // Rest of your existing code (delete row, form submission, etc.)
                tableBody.addEventListener('click', function(e) {
                    if (e.target.closest('.delete-row')) {
                        if (confirm('Are you sure you want to delete this row?')) {
                            const row = e.target.closest('tr');
                            row.remove();
                            // Renumber rows
                            const rows = tableBody.querySelectorAll('tr');
                            rows.forEach((row, index) => {
                                row.cells[0].textContent = index + 1;
                            });
                            rowCount = rows.length;
                        }
                    }
                });

                form.addEventListener('submit', function(e) {
                    {{--  e.preventDefault();  --}}

                    // Validate form
                    const rows = tableBody.querySelectorAll('tr');
                    if (rows.length === 0) {
                        Swal.fire({
                            icon: 'error',
                            title: 'No Exceptions Added',
                            text: 'Please add at least one exception before submitting',
                            confirmButtonText: 'OK'
                        });
                        return;
                    }

                    let isValid = true;
                    const invalidRows = [];

                    rows.forEach((row, index) => {
                        const title = row.querySelector('[name*="[title]"]').value;
                        const description = row.querySelector('[name*="[description]"]').value;
                        const subProcessType = row.querySelector('[name*="[sub_process_type_id]"]')
                            .value;

                        if (!title || !description || !subProcessType) {
                            isValid = false;
                            invalidRows.push(index + 1);
                            row.classList.add('table-danger');
                        } else {
                            row.classList.remove('table-danger');
                        }
                    });

                    if (!isValid) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            html: `Please fill all fields in row(s): <strong>${invalidRows.join(', ')}</strong>`,
                            confirmButtonText: 'OK'
                        });
                        return;
                    }

                    // If all valid, submit form
                    form.submit();
                });

                document.addEventListener('input', function(e) {
                    if (e.target.classList.contains('editable-textarea')) {
                        autoResizeTextarea(e.target);
                    }
                });

                function autoResizeTextarea(textarea) {
                    textarea.style.height = 'auto';
                    textarea.style.height = (textarea.scrollHeight) + 'px';
                }
            });
        </script>

        <style>
            .has-error .form-control,
            .has-error .form-select,
            .has-error .select2-selection {
                border-color: #fa5c7c !important;
            }

            .has-error label {
                color: #fa5c7c;
            }

            #addRowBtn:disabled {
                opacity: 0.65;
                cursor: not-allowed;
            }
        </style>
    @endpush
</x-base-layout>
