<x-base-layout>
    @php
    //dd($pendingException);
    $pendingException = $exceptions[0] ?? null;
    $batchId = $pendingException->exceptionBatchId ?? '';
    $processTypeId = $pendingException->processTypeId ?? '';
    $departmentId = $pendingException->departmentId ?? '';
    $requestDate = isset($pendingException->requestDate)
    ? Carbon\Carbon::parse($pendingException->requestDate)->format('Y-m-d')
    : '';
    $employeeName = session('user_name') ?? 'Unknown User';

    // Ensure exceptions property exists
    $allowedStatuses = ['PENDING','RESOLVED', 'NOT-RESOLVED', 'APPROVED'];

    $exceptions =
    isset($pendingException->exceptions) && is_iterable($pendingException->exceptions)
    ? array_filter($pendingException->exceptions, function ($exception) use ($allowedStatuses) {
    return isset($exception->status) && in_array($exception->status, $allowedStatuses);
    })
    : [];

    //$exceptions = $exceptions->exceptions ?? [];
    @endphp

    <div class="container-fluid px-1">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18"><a
                            href="{{ route('group.exception.enquiry.list') }}">Exceptions</a>
                        >
                        {{ $pendingException->submittedBy ?? '' }} >
                        {{ $pendingException->exceptionBatch->activityGroupName ?? '' }} > <a href="#">{{
                            $pendingException->departmentName ?? '' }}</a></h4>
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
                                    <option value="{{ $processType->id }}" @selected($processType->id ===
                                        $processTypeId)>
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
        {{-- {{ dd($exceptions[0]->exceptions) }} --}}
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
                            <textarea disabled class="form-control editable-textarea" rows="3"
                                name="exceptionDescription"
                                placeholder="Enter exception description">{{ $exceptionItem->exception }}</textarea>
                        </td>
                        <td>
                            <textarea disabled class="form-control editable-textarea" rows="3"
                                name="exceptionDescription"
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
                                <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal"
                                    data-bs-target="#fileAttachmentsModal-{{ $exceptionItem->id }}"
                                    title="File Attachments">
                                    <i class="bx bx-paperclip"></i>
                                    <span class="badge rounded-full bg-dark ms-1">{{ count($exceptionItem->fileAttached
                                        ?? []) }}</span>
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
        {{-- file-attachments-modal.blade.php --}}
        <div class="modal fade" id="fileAttachmentsModal-{{ $exceptionItem->id }}" tabindex="-1"
            aria-labelledby="fileAttachmentsModalLabel-{{ $exceptionItem->id }}" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h5 class="modal-title" id="fileAttachmentsModalLabel-{{ $exceptionItem->id }}">
                            File Attachments - Exception #{{ $loop->iteration }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <!-- Modal Body -->
                    <div class="modal-body">
                        <!-- Existing Files Section -->
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="mb-0">
                                    <i class="bx bx-file me-2"></i>Attached Files
                                    <span class="badge bg-secondary ms-2">
                                        {{ count($exceptionItem->fileAttached ?? []) }}
                                    </span>
                                </h6>
                            </div>

                            <div class="card">
                                <div class="card-body">
                                    <form id="file-upload-form-{{ $exceptionItem->id }}"
                                        action="{{ route('exception.file.upload', $exceptionItem->id) }}" method="POST"
                                        enctype="multipart/form-data">
                                        @csrf
                                        <label class="form-label">Attach Files</label>

                                        <!-- Hidden file input -->
                                        <input type="file" id="file-input-{{ $exceptionItem->id }}" name="files[]"
                                            multiple
                                            accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.gif,.txt,.zip,.rar"
                                            style="display: none;">

                                        <!-- Dropzone area -->
                                        <div id="file-upload-dropzone-{{ $exceptionItem->id }}" class="dropzone"
                                            style="border: 2px dashed #0087F7; border-radius: 5px; background: white; cursor: pointer; min-height: 150px;"
                                            onclick="document.getElementById('file-input-{{ $exceptionItem->id }}').click();">
                                            <div class="dz-message needsclick"
                                                style="margin: 2em 0; text-align: center;">
                                                <div class="mb-3">
                                                    <i class="display-4 text-muted bx bxs-cloud-upload"></i>
                                                </div>
                                                <h4 style="margin-bottom: 10px;">Drop files here or click to upload.
                                                </h4>
                                                <span class="text-muted">Maximum file size: 5MB</span>
                                            </div>
                                            <!-- File preview area -->
                                            <div id="file-preview-{{ $exceptionItem->id }}" class="mt-3"></div>
                                        </div>
                                        <div class="mt-4 text-end">
                                            <!-- Made button ID unique and initially hidden -->
                                            <button type="button" id="upload-button-{{ $exceptionItem->id }}"
                                                class="btn btn-primary" style="display: none;">Upload
                                                File</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <h4>Files</h4>
                                <div class="mt-4 mb-4" style="background-color: gray; height: 1px;"></div>
                                <div class="row">
                                    <!-- Fixed: Use consistent variable name -->
                                    @forelse ($exceptionItem->fileAttached ?? [] as $file)
                                    <div class="col-xl-4 mb-3" id="file-{{ $file->id }}">
                                        <div class="file-info p-3 border rounded">
                                            <p><strong>{{ $file->fileName }}</strong></p>
                                            <p class="text-muted">{{ $file->uploadDate }}</p>
                                            <p class="text-muted"><strong>Uploaded By: </strong>{{ $file->uploadedBy }}
                                            </p>

                                            <div class="d-flex justify-content-between">
                                                <!-- Download Button -->
                                                <button onclick="downloadFile('{{ $file->id }}')"
                                                    class="btn btn-sm btn-primary">
                                                    <i class="fas fa-download"></i> Download
                                                </button>

                                                <!-- Delete Button -->
                                                @if ($file->uploadedBy == $employeeName)
                                                <button onclick="deleteFile('{{ $file->id }}')"
                                                    class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i> Remove
                                                </button>
                                                @else
                                                <div></div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="text-center text-muted py-4">
                                        <i class="bx bx-file fs-1 text-muted"></i>
                                        <p class="mb-0">No files attached yet</p>
                                        <small>Upload files to share with your team</small>
                                    </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @push('styles')
        <style>
            .dropzone {
                border: 2px dashed #0087F7 !important;
                border-radius: 5px !important;
                background: white !important;
                cursor: pointer !important;
                min-height: 150px !important;
                transition: all 0.3s ease;
            }

            .dropzone:hover {
                border-color: #0056b3 !important;
                background: #f8f9fa !important;
            }

            .dropzone.dz-drag-hover {
                border-color: #28a745 !important;
                background: #d4edda !important;
            }

            .dropzone .dz-message {
                margin: 2em 0 !important;
                text-align: center !important;
            }

            .dropzone .dz-message .needsclick {
                cursor: pointer !important;
            }

            .dropzone .dz-preview {
                margin: 10px;
            }

            .dropzone .dz-preview .dz-remove {
                cursor: pointer;
                color: #dc3545;
            }

            .dropzone .dz-preview .dz-remove:hover {
                text-decoration: underline;
            }
        </style>
        @endpush

        @push('scripts')
        <script>
            // Initialize dropzone when modal is shown
            $('#fileAttachmentsModal-{{ $exceptionItem->id }}').on('shown.bs.modal', function() {
                initializeFileUpload{{$exceptionItem->id}}();
            });

            function initializeFileUpload{{$exceptionItem->id}}() {
                var fileInput = document.getElementById('file-input-{{ $exceptionItem->id }}');
                var dropzoneArea = document.getElementById('file-upload-dropzone-{{ $exceptionItem->id }}');
                var previewArea = document.getElementById('file-preview-{{ $exceptionItem->id }}');
                var uploadButton = document.getElementById('upload-button-{{ $exceptionItem->id }}');
                var selectedFiles = [];

                console.log("File upload initialized for exception {{ $exceptionItem->id }}");

                // Handle file input change
                fileInput.addEventListener('change', function(e) {
                    var files = Array.from(e.target.files);
                    console.log("Files selected:", files.length);

                    if (files.length > 0) {
                        selectedFiles = files;
                        displayFilePreview(files);
                    }
                });

                // Handle drag and drop
                dropzoneArea.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    dropzoneArea.style.borderColor = '#28a745';
                    dropzoneArea.style.background = '#d4edda';
                });

                dropzoneArea.addEventListener('dragleave', function(e) {
                    e.preventDefault();
                    dropzoneArea.style.borderColor = '#0087F7';
                    dropzoneArea.style.background = 'white';
                });

                dropzoneArea.addEventListener('drop', function(e) {
                    e.preventDefault();
                    dropzoneArea.style.borderColor = '#0087F7';
                    dropzoneArea.style.background = 'white';

                    var files = Array.from(e.dataTransfer.files);
                    console.log("Files dropped:", files.length);

                    if (files.length > 0) {
                        selectedFiles = files;
                        displayFilePreview(files);
                    }
                });

                // Display file preview
                function displayFilePreview(files) {
                    previewArea.innerHTML = '';

                    files.forEach(function(file, index) {
                        var fileDiv = document.createElement('div');
                        fileDiv.className = 'file-preview-item d-flex justify-content-between align-items-center p-2 mb-2 border rounded';
                        fileDiv.innerHTML = `
                        <div class="d-flex align-items-center">
                            <i class="bx bx-file me-2"></i>
                            <span class="file-name">${file.name}</span>
                            <span class="text-muted ms-2">(${formatFileSize(file.size)})</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFile${{{ $exceptionItem->id }}}(${index})">
                            <i class="bx bx-x"></i>
                        </button>
                    `;
                        previewArea.appendChild(fileDiv);
                    });

                    // Show upload button
                    uploadButton.style.display = 'inline-block';
                }

                // Remove file from selection
                window['removeFile{{ $exceptionItem->id }}'] = function(index) {
                    selectedFiles.splice(index, 1);
                    if (selectedFiles.length > 0) {
                        displayFilePreview(selectedFiles);
                    } else {
                        previewArea.innerHTML = '';
                        uploadButton.style.display = 'none';
                    }
                };

                // Handle upload button click
                uploadButton.addEventListener('click', function(e) {
                    e.preventDefault();
                    console.log("Upload button clicked, files to upload:", selectedFiles.length);

                    if (selectedFiles.length === 0) {
                        Swal.fire({
                            icon: "warning"
                            , title: "No Files"
                            , text: "Please select files to upload first"
                            , toast: true
                            , position: "top-end"
                            , showConfirmButton: false
                            , timer: 3000
                        });
                        return;
                    }

                    uploadFiles(selectedFiles);
                });

                // Upload files function
                function uploadFiles(files) {
                    var formData = new FormData();

                    // Add files to form data
                    files.forEach(function(file) {
                        formData.append('files[]', file);
                    });

                    // Add CSRF token
                    formData.append('_token', '{{ csrf_token() }}');

                    // Show loading state
                    uploadButton.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Uploading...';
                    uploadButton.disabled = true;

                    $.ajax({
                        url: "{{ route('exception.file.upload', $exceptionItem->id) }}"
                        , method: "POST"
                        , data: formData
                        , processData: false
                        , contentType: false
                        , success: function(response) {
                            console.log("Upload success:", response);

                            // Reset button
                            uploadButton.innerHTML = 'Upload File';
                            uploadButton.disabled = false;
                            uploadButton.style.display = 'none';

                            if (response.status === "success") {
                                Swal.fire({
                                    icon: "success"
                                    , title: "Success"
                                    , text: response.message || "Files uploaded successfully"
                                    , toast: true
                                    , position: "top-end"
                                    , showConfirmButton: false
                                    , timer: 3000
                                });

                                // Clear selections and refresh
                                selectedFiles = [];
                                previewArea.innerHTML = '';
                                fileInput.value = '';

                                // Refresh the page to show new files
                                setTimeout(function() {
                                    window.location.reload();
                                }, 1500);
                            } else {
                                Swal.fire({
                                    icon: "error"
                                    , title: "Error"
                                    , text: response.message || "Upload failed"
                                    , toast: true
                                    , position: "top-end"
                                    , showConfirmButton: false
                                    , timer: 3000
                                });
                            }
                        }
                        , error: function(xhr) {
                            console.log("Upload error:", xhr);

                            // Reset button
                            uploadButton.innerHTML = 'Upload File';
                            uploadButton.disabled = false;

                            var errorMessage = "An error occurred during upload";

                            try {
                                var response = JSON.parse(xhr.responseText);
                                if (response.message) {
                                    errorMessage = response.message;
                                } else if (response.errors) {
                                    errorMessage = Object.values(response.errors).join(', ');
                                }
                            } catch (e) {
                                if (xhr.responseText) {
                                    errorMessage = xhr.responseText;
                                }
                            }

                            Swal.fire({
                                icon: "error"
                                , title: "Upload Failed"
                                , text: errorMessage
                                , toast: true
                                , position: "top-end"
                                , showConfirmButton: false
                                , timer: 5000
                            });
                        }
                    });
                }

                // Format file size helper
                function formatFileSize(bytes) {
                    if (bytes === 0) return '0 Bytes';
                    const k = 1024;
                    const sizes = ['Bytes', 'KB', 'MB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                }
            }

            // Download file function
            function downloadFile(fileId) {
                $.ajax({
                    url: "{{ route('exception.file.download', ':id') }}".replace(':id', fileId)
                    , method: "GET"
                    , headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                    }
                    , success: function(response) {
                        if (response.status === "success") {
                            // Create download link
                            let link = document.createElement("a");
                            link.href = `data:application/octet-stream;base64,${response.fileData}`;
                            link.download = response.fileName;
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                        } else {
                            Swal.fire({
                                icon: "error"
                                , title: "Download Failed"
                                , text: response.message
                                , toast: true
                                , position: "top-end"
                                , showConfirmButton: false
                                , timer: 3000
                            });
                        }
                    }
                    , error: function(xhr) {
                        Swal.fire({
                            icon: "error"
                            , title: "Error"
                            , text: "Failed to download file. Please try again."
                            , toast: true
                            , position: "top-end"
                            , showConfirmButton: false
                            , timer: 3000
                        });
                    }
                });
            }

            // Delete file function
            function deleteFile(fileId) {
                Swal.fire({
                    title: "Are you sure?"
                    , text: "This file will be permanently deleted!"
                    , icon: "warning"
                    , showCancelButton: true
                    , confirmButtonColor: "#d33"
                    , cancelButtonColor: "#3085d6"
                    , confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('exception.file.delete', ':id') }}".replace(':id', fileId)
                            , method: "DELETE"
                            , headers: {
                                "X-CSRF-TOKEN": "{{ csrf_token() }}"
                            }
                            , success: function(response) {
                                if (response.status === "success") {
                                    Swal.fire({
                                        icon: "success"
                                        , title: "Deleted!"
                                        , text: response.message
                                        , toast: true
                                        , position: "top-end"
                                        , showConfirmButton: false
                                        , timer: 3000
                                    });

                                    // Remove file from UI with animation
                                    $(`#file-${fileId}`).fadeOut(300, function() {
                                        $(this).remove();
                                    });
                                } else {
                                    Swal.fire({
                                        icon: "error"
                                        , title: "Error"
                                        , text: response.message
                                        , toast: true
                                        , position: "top-end"
                                        , showConfirmButton: false
                                        , timer: 3000
                                    });
                                }
                            }
                            , error: function(xhr) {
                                Swal.fire({
                                    icon: "error"
                                    , title: "Error"
                                    , text: "Failed to delete file. Please try again."
                                    , toast: true
                                    , position: "top-end"
                                    , showConfirmButton: false
                                    , timer: 3000
                                });
                            }
                        });
                    }
                });
            }

        </script>
        @endpush



        {{-- VIEW EXCEPTION DETAILS MODAL --}}

        {{-- partials.auditee.view-exception-modal --}}
        <div class="modal fade" id="viewExceptionModal-{{ $exceptionItem->id }}" tabindex="-1"
            aria-labelledby="viewExceptionModalLabel-{{ $exceptionItem->id }}" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h5 class="modal-title" id="viewExceptionModalLabel-{{ $exceptionItem->id }}">
                            Exception Analysis - Exception #{{ $loop->iteration }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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

                                        <!-- Recommendation by Auditor -->
                                        <div class="mb-4">
                                            <h6 class="text-muted mb-2">Recommendation by Auditor</h6>
                                            <p class="mb-0">{{ $exceptionItem->recommendation ?: 'Not specified' }}
                                            </p>
                                        </div>

                                        <!-- Risk Involved -->
                                        <div class="mb-4">
                                            <h6 class="text-muted mb-2">Risk Involved</h6>

                                            <p class="mb-0">{{ $exceptionItem->riskAnalysis ?: 'Not specified' }}
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
                                                        {{ $exceptionItem->occurrenceDate ?
                                                        \Carbon\Carbon::parse($exceptionItem->occurrenceDate)->format('M
                                                        d, Y') : 'Not specified' }}
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
                                            <span class="badge
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
                                                {{
                                                \Carbon\Carbon::parse($exceptionItem->proposeResolutionDate)->format('M
                                                d, Y') }}
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
                                                {{ \Carbon\Carbon::parse($exceptionItem->resolutionDate)->format('M d,
                                                Y') }}
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
                                                {{ \Carbon\Carbon::parse($exceptionItem->createdAt)->format('M d, Y \a\t
                                                g:i A') }}
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
