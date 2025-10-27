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
            {{--  <button data-bs-toggle="modal" data-bs-target="#addSubProcessTypeModal"
                class="btn btn-primary btn-rounded waves-effect waves-light">
                <i class="bx bx-plus"></i> Add subProcessType
            </button>  --}}
            <button id="addRowBtn" class="btn btn-success btn-rounded waves-effect waves-light">
                <i class="bx bx-plus"></i> Add Row
            </button>
        </div>

        {{-- modal for adding subProcessType --}}
        <div class="modal fade" id="addSubProcessTypeModal" tabindex="-1" role="dialog"
            aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-top">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addSubProcessTypeModalLabel">Add Sub Process Type</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="addSubProcessTypeForm">
                            @csrf
                            <div class="mb-3">
                                <label for="subProcessTypeName" class="form-label">Sub Process Type Name</label>
                                <input type="text" class="form-control" id="subProcessTypeName" name="name" required>
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
                                <button type="submit" class="btn btn-primary" id="submitSubProcessType">
                                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"
                                        style="display: none;"></span>
                                    Add Sub Process Type
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal for File Attachments --}}
        <div class="modal fade" id="fileAttachmentsModal" tabindex="-1" role="dialog"
            aria-labelledby="fileAttachmentsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="fileAttachmentsModalLabel">
                            File Attachments - Exception <span id="currentRowNumber"></span>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="fileDescription" class="form-label">File Description (Optional)</label>
                            <input type="text" class="form-control" id="fileDescription"
                                placeholder="Enter a description for the files">
                        </div>
                        <div class="mb-3">
                            <label for="fileInput" class="form-label">Choose Files</label>
                            <input type="file" class="form-control" id="fileInput" multiple
                                accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png,.gif">
                            <small class="text-muted">Max file size: 10MB per file. Accepted formats: PDF, DOC, DOCX,
                                XLS, XLSX, JPG, PNG, GIF</small>
                        </div>
                        <div class="mb-3">
                            <button type="button" class="btn btn-primary" id="addFilesBtn">
                                <i class="bx bx-plus"></i> Add Files
                            </button>
                        </div>
                        <div id="filesList" class="mt-3">
                            <h6>Attached Files:</h6>
                            <div class="list-group" id="attachedFilesList">
                                <!-- Files will be listed here -->
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
                                <label class="form-label">Group/Branch</label>
                                <select class="form-select select2" id="groupFilter">
                                    <option>Select.....</option>
                                    @foreach ($groups as $group)
                                        <option value="{{ $group->id }}">{{ $group->name }}</option>
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

        <form id="bulkExceptionForm" action="{{ route('bulk.exception.create') }}" method="POST"
            enctype="multipart/form-data">
            @csrf

            <input type="hidden" name="processTypeId" id="formProcessTypeId">
            <input type="hidden" name="departmentId" id="formDepartmentId">
            <input type="hidden" name="exceptionBatchId" id="formBatchId">
            <input type="hidden" name="activityGroupId" id="formGroupId">
            <input type="hidden" name="occurrenceDate" id="formOccurrenceDate">

            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0" id="exceptionsTable">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Exception Title</th>
                            <th>Exception Description</th>
                            {{--  <th scope="col">Sub Category</th>  --}}
                            {{--  <th scope="col">Attachments</th>  --}}
                            <th style="width: 1rem">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Rows will be added dynamically here -->
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-3 mb-5">
                <button type="submit" id="submitExceptionsBtn"
                    class="btn btn-primary btn-rounded waves-effect waves-light">
                    <span class="btn-text">
                        <i class="bx bx-save"></i> Submit All Exceptions
                    </span>
                    <span class="btn-loading" style="display: none;">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        Processing...
                    </span>
                </button>
            </div>
        </form>
    </div>


    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="{{ asset('assets/js/ajax.jquery.min.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                let rowCount = 0;
                let currentRowId = null;
                const tableBody = document.querySelector('#exceptionsTable tbody');
                const form = document.getElementById('bulkExceptionForm');
                const addRowBtn = document.getElementById('addRowBtn');
                const requiredFilters = ['#batchFilter', '#processTypeFilter', '#departmentFilter',
                    '#occurrenceDateFilter', '#groupFilter'
                ];

                // Object to store files for each row
                const rowFiles = {};

                // Initially disable the add row button
                addRowBtn.disabled = true;

                // AJAX Sub Process Type Form Submission
                {{--  $('#addSubProcessTypeForm').on('submit', function (e) {
                    e.preventDefault();

                    const formData = new FormData(this);
                    const submitBtn = $('#submitSubProcessType');
                    const spinner = submitBtn.find('.spinner-border');
                    const btnText = submitBtn.text().trim();

                    // Show loading state
                    spinner.show();
                    submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Adding...');

                    $.ajax({
                        url: "{{ route('sub.process.type') }}",
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function (response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: 'Sub Process Type added successfully',
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true,
                            });

                            $('#addSubProcessTypeForm')[0].reset();
                            $('#addSubProcessTypeModal').modal('hide');

                            const currentProcessTypeId = $('#processTypeFilter').val();
                            if (currentProcessTypeId && currentProcessTypeId === response.processTypeId) {
                                loadSubProcessTypes(currentProcessTypeId, function (subProcessTypes) {
                                    document.querySelectorAll('#exceptionsTable tbody tr').forEach(row => {
                                        updateRowSubProcessTypes(row, subProcessTypes);
                                    });
                                });
                            }
                        },
                        error: function (xhr) {
                            let errorMessage = 'An error occurred while adding the sub process type';

                            if (xhr.responseJSON) {
                                if (xhr.responseJSON.message) {
                                    errorMessage = xhr.responseJSON.message;
                                } else if (xhr.responseJSON.errors) {
                                    const errors = Object.values(xhr.responseJSON.errors).flat();
                                    errorMessage = errors.join(', ');
                                }
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: errorMessage,
                                confirmButtonText: 'OK'
                            });
                        },
                        complete: function () {
                            spinner.hide();
                            submitBtn.prop('disabled', false).text(btnText);
                        }
                    });
                });  --}}

                // Reset form when modal is closed
                {{--  $('#addSubProcessTypeModal').on('hidden.bs.modal', function () {
                    $('#addSubProcessTypeForm')[0].reset();
                    $('#submitSubProcessType').prop('disabled', false).text('Add Sub Process Type');
                    $('#submitSubProcessType').find('.spinner-border').hide();
                });  --}}

                // Handle file attachment modal opening
                $(document).on('click', '.attach-files-btn', function () {
                    currentRowId = $(this).data('row-id');
                    $('#currentRowNumber').text(currentRowId);

                    // Clear previous inputs
                    $('#fileInput').val('');
                    $('#fileDescription').val('');

                    // Display existing files for this row
                    displayAttachedFiles(currentRowId);
                });

                // Reset modal when closed
                $('#fileAttachmentsModal').on('hidden.bs.modal', function () {
                    currentRowId = null;
                    $('#fileInput').val('');
                    $('#fileDescription').val('');
                });

                // Add files button handler
                $('#addFilesBtn').on('click', function () {
                    const fileInput = document.getElementById('fileInput');
                    const fileDescription = document.getElementById('fileDescription').value;
                    const files = fileInput.files;

                    if (files.length === 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'No Files Selected',
                            text: 'Please select at least one file',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                        return;
                    }

                    // Validate file sizes
                    let hasOversizedFile = false;
                    Array.from(files).forEach(file => {
                        if (file.size > 10 * 1024 * 1024) { // 10MB
                            hasOversizedFile = true;
                        }
                    });

                    if (hasOversizedFile) {
                        Swal.fire({
                            icon: 'error',
                            title: 'File Too Large',
                            text: 'One or more files exceed the 10MB limit',
                            confirmButtonText: 'OK'
                        });
                        return;
                    }

                    // Initialize row files array if not exists
                    if (!rowFiles[currentRowId]) {
                        rowFiles[currentRowId] = [];
                    }

                    // Add files to the row's collection
                    Array.from(files).forEach(file => {
                        rowFiles[currentRowId].push({
                            file: file,
                            description: fileDescription || file.name
                        });
                    });

                    // Update the attachment badge
                    updateAttachmentBadge(currentRowId);

                    // Display the updated file list
                    displayAttachedFiles(currentRowId);

                    // Clear inputs
                    fileInput.value = '';
                    document.getElementById('fileDescription').value = '';

                    Swal.fire({
                        icon: 'success',
                        title: 'Files Added',
                        text: `${files.length} file(s) added successfully`,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000
                    });
                });

                // Display attached files in modal
                function displayAttachedFiles(rowId) {
                    const filesList = document.getElementById('attachedFilesList');
                    filesList.innerHTML = '';

                    if (!rowFiles[rowId] || rowFiles[rowId].length === 0) {
                        filesList.innerHTML = '<p class="text-muted">No files attached yet</p>';
                        return;
                    }

                    rowFiles[rowId].forEach((fileObj, index) => {
                        const fileItem = document.createElement('div');
                        fileItem.className = 'list-group-item d-flex justify-content-between align-items-center';
                        fileItem.innerHTML = `
                                <div>
                                    <i class="bx bx-file me-2"></i>
                                    <strong>${fileObj.file.name}</strong>
                                    <br>
                                    <small class="text-muted">${fileObj.description}</small>
                                    <br>
                                    <small class="text-muted">${(fileObj.file.size / 1024).toFixed(2)} KB</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-danger remove-file-btn" data-row-id="${rowId}" data-file-index="${index}">
                                    <i class="bx bx-trash"></i>
                                </button>
                            `;
                        filesList.appendChild(fileItem);
                    });
                }

                // Remove file handler
                $(document).on('click', '.remove-file-btn', function () {
                    const rowId = $(this).data('row-id');
                    const fileIndex = $(this).data('file-index');

                    rowFiles[rowId].splice(fileIndex, 1);
                    displayAttachedFiles(rowId);
                    updateAttachmentBadge(rowId);

                    Swal.fire({
                        icon: 'success',
                        title: 'File Removed',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 2000
                    });
                });

                // Update attachment badge
                function updateAttachmentBadge(rowId) {
                    const row = document.querySelector(`tr[data-row-id="${rowId}"]`);
                    const badge = row.querySelector('.attachment-badge');
                    const count = rowFiles[rowId] ? rowFiles[rowId].length : 0;

                    if (count > 0) {
                        badge.textContent = count;
                        badge.style.display = 'inline-block';
                    } else {
                        badge.style.display = 'none';
                    }
                }

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

                    addRowBtn.disabled = !isValid;
                    return isValid;
                }

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
                    document.querySelector(filter).addEventListener('change', function () {
                        validateFilters();
                        this.closest('.col-md-3').classList.remove('has-error');
                    });
                });

                // Function to load sub-process types for a process type
                {{--  function loadSubProcessTypes(processTypeId, callback) {
                    if (!processTypeId) return;

                    const url = "{{ url('/get-sub-process-types') }}/" + processTypeId;

                    $.ajax({
                        url: url,
                        type: 'GET',
                        success: function (data) {
                            if (callback) callback(data);
                        },
                        error: function (xhr) {
                            console.error('Error loading sub-process types:', xhr.responseText);
                            console.error('Request URL:', url);
                            Swal.fire('Error', 'Failed to load sub-process types', 'error');
                        }
                    });
                }  --}}

                // Update a row's sub-process dropdown
                {{--  function updateRowSubProcessTypes(row, subProcessTypes) {
                    const select = row.querySelector('.sub-process-type');
                    const currentValue = select.value;

                    select.innerHTML = '<option value="">Select...</option>';
                    subProcessTypes.forEach(subType => {
                        select.innerHTML += `<option value="${subType.id}">${subType.name}</option>`;
                    });

                    if (currentValue && subProcessTypes.some(st => st.id == currentValue)) {
                        select.value = currentValue;
                    }
                }  --}}

                // When process type filter changes
                $('#processTypeFilter').change(function () {
                    const processTypeId = $(this).val();
                    document.getElementById('formProcessTypeId').value = processTypeId;

                    if (!processTypeId) return;

                    loadSubProcessTypes(processTypeId, function (subProcessTypes) {
                        document.querySelectorAll('#exceptionsTable tbody tr').forEach(row => {
                            updateRowSubProcessTypes(row, subProcessTypes);
                        });
                    });
                });

                // Add new row with validation
                addRowBtn.addEventListener('click', function () {
                    if (!validateFilters()) {
                        showValidationError();
                        return;
                    }

                    const processTypeId = document.getElementById('processTypeFilter').value;

                    loadSubProcessTypes(processTypeId, function (subProcessTypes) {
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
                            {{--  <td>
                                <select class="form-select sub-process-type"
                                        name="exceptions[${rowCount}][subProcessTypeId]">
                                    <option value="">Loading...</option>
                                </select>
                            </td>  --}}
                            {{--  <td>

                            </td>  --}}
                            <td class="text-center">
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-warning attach-files-btn position-relative"
                                        data-bs-toggle="modal"
                                        data-bs-target="#fileAttachmentsModal"
                                        data-row-id="${rowCount}"
                                        title="File Attachments">
                                    <i class="bx bx-paperclip"></i>
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger attachment-badge" style="display: none;">
                                        0
                                    </span>
                                </button>

                                <button type="button" class="btn btn-sm btn-danger delete-row">
                                    <i class="bx bxs-trash"></i> Delete
                                </button>
                                </div
                            </td>
                        `;

                        tableBody.appendChild(newRow);
                        updateRowSubProcessTypes(newRow, subProcessTypes);
                        newRow.querySelector('textarea').focus();

                        // Update hidden form fields
                        document.getElementById('formProcessTypeId').value = processTypeId;
                        document.getElementById('formGroupId').value = document.getElementById('groupFilter').value;
                        document.getElementById('formDepartmentId').value = document.getElementById('departmentFilter').value;
                        document.getElementById('formBatchId').value = document.getElementById('batchFilter').value;
                        document.getElementById('formOccurrenceDate').value = document.getElementById('occurrenceDateFilter').value;
                    });
                });

                // Delete row functionality
                tableBody.addEventListener('click', function (e) {
                    if (e.target.closest('.delete-row')) {
                        if (confirm('Are you sure you want to delete this row?')) {
                            const row = e.target.closest('tr');
                            const rowId = row.dataset.rowId;

                            // Remove files associated with this row
                            delete rowFiles[rowId];

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

                // Form submission with file handling
                form.addEventListener('submit', function (e) {
                    e.preventDefault();

                    const submitBtn = document.getElementById('submitExceptionsBtn');
                    const btnText = submitBtn.querySelector('.btn-text');
                    const btnLoading = submitBtn.querySelector('.btn-loading');

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
                        const title = row.querySelector('[name*="[exceptionTitle]"]').value;
                        const description = row.querySelector('[name*="[exception]"]').value;
                        {{--  const subProcessType = row.querySelector('[name*="[subProcessTypeId]"]').value;  --}}

                        {{--  if (!title || !description || !subProcessType) {  --}}
                        if (!title || !description) {
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

                    // Show loading state
                    submitBtn.disabled = true;
                    btnText.style.display = 'none';
                    btnLoading.style.display = 'inline-block';

                    // Show loading alert
                    Swal.fire({
                        title: 'Processing...',
                        html: 'Please wait while we submit your exceptions',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        allowEnterKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    // Create FormData
                    const formData = new FormData();

                    // Add CSRF token
                    formData.append('_token', document.querySelector('input[name="_token"]').value);

                    // Add hidden form fields
                    formData.append('processTypeId', document.getElementById('formProcessTypeId').value);
                    formData.append('departmentId', document.getElementById('formDepartmentId').value);
                    formData.append('exceptionBatchId', document.getElementById('formBatchId').value);
                    formData.append('activityGroupId', document.getElementById('formGroupId').value);
                    formData.append('occurrenceDate', document.getElementById('formOccurrenceDate').value);

                    // Build exceptions array with proper sequential indexing
                    rows.forEach((row, index) => {
                        const rowId = row.dataset.rowId;

                        // Add exception data
                        const title = row.querySelector('[name*="[exceptionTitle]"]').value;
                        const description = row.querySelector('[name*="[exception]"]').value;
                        {{--  const subProcessTypeId = row.querySelector('[name*="[subProcessTypeId]"]').value;  --}}

                        formData.append(`exceptions[${index}][exceptionTitle]`, title);
                        formData.append(`exceptions[${index}][exception]`, description);
                        {{--  formData.append(`exceptions[${index}][subProcessTypeId]`, subProcessTypeId);  --}}

                        // Add files if any
                        if (rowFiles[rowId] && rowFiles[rowId].length > 0) {
                            rowFiles[rowId].forEach((fileObj, fileIndex) => {
                                formData.append(`exceptions[${index}][files][${fileIndex}]`, fileObj.file);
                            });
                            // Add file description
                            formData.append(`exceptions[${index}][fileDescription]`, rowFiles[rowId][0].description);
                        }
                    });

                    // Submit form via AJAX
                    $.ajax({
                        url: form.action,
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function (response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: 'Bulk exceptions created successfully',
                                confirmButtonText: 'OK',
                                timer: 3000,
                                timerProgressBar: true
                            }).then(() => {
                                window.location.reload();
                            });
                        },
                        error: function (xhr) {
                            let errorMessage = 'Failed to create exceptions';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMessage = xhr.responseJSON.message;
                            } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                                const errors = Object.values(xhr.responseJSON.errors).flat();
                                errorMessage = errors.join('<br>');
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                html: errorMessage,
                                confirmButtonText: 'OK'
                            });
                        },
                        complete: function () {
                            // Reset button state
                            submitBtn.disabled = false;
                            btnText.style.display = 'inline-block';
                            btnLoading.style.display = 'none';
                        }
                    });
                });

                // Auto-resize textareas
                document.addEventListener('input', function (e) {
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

            .spinner-border-sm {
                width: 1rem;
                height: 1rem;
            }

            .attach-files-btn {
                position: relative;
            }

            .attachment-badge {
                font-size: 0.65rem;
            }

            #filesList {
                max-height: 300px;
                overflow-y: auto;
            }

            .list-group-item {
                padding: 0.75rem 1rem;
            }
        </style>
    @endpush
</x-base-layout>
