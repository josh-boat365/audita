<x-base-layout>
    @php
$pendingException = $exception;
$batchId = $pendingException->exceptionBatchId ?? '';
$groupId = $pendingException->activityGroupId ?? '';

$processTypeId = $pendingException->processTypeId ?? '';
$departmentId = $pendingException->departmentId ?? '';
$requestDate = isset($pendingException->requestDate)
    ? Carbon\Carbon::parse($pendingException->requestDate)->format('Y-m-d')
    : '';

$employeeRoleId = App\Http\Controllers\ExceptionManipulationController::getLoggedInUserInformation()->empRoleId;
$auditManagerRoleId = 15;
$employeeName = session('user_name') ?? 'Unknown User';
    @endphp

    <div class="container-fluid px-1">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18"><a href="{{ route('exception.supervisor.list') }}">Exceptions</a> >
                        {{ $pendingException->submittedBy ?? '' }} >
                        {{ $pendingException->exceptionBatch->activityGroupName ?? '' }} > <a
                            href="#">{{ $pendingException->departmentName ?? '' }}</a>
                    </h4>
                </div>
            </div>
        </div>
        <!-- end page title -->


        <div class="d-flex gap-2 mb-4 justify-content-end">
            @if ($employeeRoleId == $auditManagerRoleId)
                <!-- Review Batch Button -->
                <button type="button" class="btn btn-warning btn-rounded waves-effect waves-light" id="reviewBatchBtn">
                    Push Batch For Review
                </button>
            @else
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
            @endif

        </div>



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
                                            {{ $batch->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Group/Branch</label>
                                <select class="form-select select2" id="groupFilter">
                                    <option>Select.....</option>
                                    @foreach ($groups as $group)
                                        <option value="{{ $group->id }}" @selected($group->id === $groupId)>{{ $group->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Process Types</label>
                                <select class="form-select select2" id="processTypeFilter">
                                    <option>Select.....</option>
                                    @foreach ($processTypes as $processType)
                                        <option value="{{ $processType->id }}" @selected($processType->id === $processTypeId)>
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
        {{--  {{ dd($pendingException) }}  --}}
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
                                                        <option value="{{ $subProcessType->id }}"
                                                            @selected($subProcessType->id === $exceptionItem->subProcessTypeId)>
                                                            {{ $subProcessType->name }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                {{-- <button type="button" class="btn btn-secondary btn-sm push-for-amendment-btn"
                                                    data-exception-id="{{ $exceptionItem->id }}">
                                                    <i class="bx bx-check"></i> Push for Edit
                                                </button> --}}
                                                <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="modal"
                                                    data-bs-target="#fileAttachmentsModal-{{ $exceptionItem->id }}"
                                                    title="File Attachments">
                                                    <i class="bx bx-paperclip"></i>
                                                    <span class="badge rounded-full bg-dark ms-1">{{ count($exceptionItem->fileAttached
        ?? []) }}</span>
                                                </button>
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
        <div class="modal fade" id="declineModal" tabindex="-1" aria-labelledby="declineModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="declineModalLabel">Decline Exception</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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



    @foreach ($pendingException->exceptions as $exceptionItem)
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
                                        <input type="file" id="file-input-{{ $exceptionItem->id }}" name="files[]" multiple
                                            accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.gif,.txt,.zip,.rar"
                                            style="display: none;">

                                        <!-- Dropzone area -->
                                        <div id="file-upload-dropzone-{{ $exceptionItem->id }}" class="dropzone"
                                            style="border: 2px dashed #0087F7; border-radius: 5px; background: white; cursor: pointer; min-height: 150px;"
                                            onclick="document.getElementById('file-input-{{ $exceptionItem->id }}').click();">
                                            <div class="dz-message needsclick" style="margin: 2em 0; text-align: center;">
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
                $('#fileAttachmentsModal-{{ $exceptionItem->id }}').on('shown.bs.modal', function () {
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
                    fileInput.addEventListener('change', function (e) {
                        var files = Array.from(e.target.files);
                        console.log("Files selected:", files.length);

                        if (files.length > 0) {
                            selectedFiles = files;
                            displayFilePreview(files);
                        }
                    });

                    // Handle drag and drop
                    dropzoneArea.addEventListener('dragover', function (e) {
                        e.preventDefault();
                        dropzoneArea.style.borderColor = '#28a745';
                        dropzoneArea.style.background = '#d4edda';
                    });

                    dropzoneArea.addEventListener('dragleave', function (e) {
                        e.preventDefault();
                        dropzoneArea.style.borderColor = '#0087F7';
                        dropzoneArea.style.background = 'white';
                    });

                    dropzoneArea.addEventListener('drop', function (e) {
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

                        files.forEach(function (file, index) {
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
                    window['removeFile{{ $exceptionItem->id }}'] = function (index) {
                        selectedFiles.splice(index, 1);
                        if (selectedFiles.length > 0) {
                            displayFilePreview(selectedFiles);
                        } else {
                            previewArea.innerHTML = '';
                            uploadButton.style.display = 'none';
                        }
                    };

                    // Handle upload button click
                    uploadButton.addEventListener('click', function (e) {
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
                        files.forEach(function (file) {
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
                            , success: function (response) {
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
                                    setTimeout(function () {
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
                            , error: function (xhr) {
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
                        , success: function (response) {
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
                        , error: function (xhr) {
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
                                , success: function (response) {
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
                                        $(`#file-${fileId}`).fadeOut(300, function () {
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
                                , error: function (xhr) {
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
    @endforeach


    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="{{ asset('assets/js/ajax.jquery.min.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Function to load sub-process types for a process type
                function loadSubProcessTypes(processTypeId, callback) {
                    if (!processTypeId) return;

                    $.ajax({
                        url: '/get-sub-process-types/' + processTypeId,
                        type: 'GET',
                        success: function (data) {
                            if (callback) callback(data);
                        },
                        error: function (xhr) {
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
                $('#processTypeFilter').change(function () {
                    const processTypeId = $(this).val();

                    if (!processTypeId) return;

                    loadSubProcessTypes(processTypeId, function (subProcessTypes) {
                        // Update all existing rows
                        document.querySelectorAll('#exceptionsTable tbody tr').forEach(row => {
                            updateRowSubProcessTypes(row, subProcessTypes);
                        });
                    });
                });

                // SUB-EXCPETION STATUSES -  individual rows
                document.querySelectorAll('.push-for-amendment-btn').forEach(btn => {
                    btn.addEventListener('click', function () {
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
                    btn.addEventListener('click', function () {
                        const exceptionId = this.getAttribute('data-exception-id');
                        document.getElementById('currentExceptionId').value = exceptionId;
                        document.getElementById('statusComment').value = ''; // Clear previous reason
                    });
                });

                // Confirm decline button in modal
                document.getElementById('confirmDeclineBtn').addEventListener('click', function () {
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
                 // SUBMIT BATCH APPROVAL ACTION - PARENT BATCH STATUS
                const approveBatchBtn = document.getElementById('approveBatchBtn');
                const reviewBatchBtn = document.getElementById('reviewBatchBtn');
                const amendBatchBtn = document.getElementById('amendBatchBtn');
                const declineBatchForm = document.getElementById('declineBatchForm');

                // Approve Batch Handler
                if (approveBatchBtn) {
                    approveBatchBtn.addEventListener('click', function (e) {
                        e.preventDefault(); // Prevent any default behavior

                        Swal.fire({
                            title: 'Approve Batch',
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
                }

                // Review Batch Handler - FIXED
                if (reviewBatchBtn) {
                    reviewBatchBtn.addEventListener('click', function (e) {
                        e.preventDefault(); // Prevent any default behavior
                        e.stopPropagation(); // Stop event bubbling

                        console.log('Review button clicked'); // Debug log

                        Swal.fire({
                            title: 'Push Batch For Review',
                            text: 'Are you sure you want to push this batch for review?',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, Push for Review',
                            cancelButtonText: 'No, Cancel',
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                submitBatchAction('REVIEW', 'Batch pushed for review');
                            }
                        });
                    });
                }

                // Amend Batch Handler
                if (amendBatchBtn) {
                    amendBatchBtn.addEventListener('click', function (e) {
                        e.preventDefault(); // Prevent any default behavior

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
                }

                // Decline Batch Form Handler
                if (declineBatchForm) {
                    declineBatchForm.addEventListener('submit', function (e) {
                        e.preventDefault(); // Prevent default form submission

                        const comment = document.getElementById('declineComment')?.value.trim();
                        if (!comment) {
                            Swal.fire('Error', 'Please provide a reason for declining', 'error');
                            document.getElementById('declineComment')?.focus();
                            return false;
                        }

                        submitBatchAction('DECLINED', comment);
                    });
                }

                // Submit Batch Action Function
                function submitBatchAction(status, comment) {
                    // Create form dynamically
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route('exception.supervisor.action') }}';
                    form.style.display = 'none';

                    // Add CSRF token
                    const csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value = document.querySelector('meta[name="csrf-token"]').content;
                    form.appendChild(csrf);

                    // Add batch ID
                    const batchId = document.createElement('input');
                    batchId.type = 'hidden';
                    batchId.name = 'batchExceptionId';
                    batchId.value = '{{ $pendingException->id ?? '' }}';
                    form.appendChild(batchId);

                    // Add status
                    const statusInput = document.createElement('input');
                    statusInput.type = 'hidden';
                    statusInput.name = 'status';
                    statusInput.value = status;
                    form.appendChild(statusInput);

                    // Add comment
                    const commentInput = document.createElement('input');
                    commentInput.type = 'hidden';
                    commentInput.name = 'statusComment';
                    commentInput.value = comment;
                    form.appendChild(commentInput);

                    // Append to body and submit
                    document.body.appendChild(form);

                    console.log('Submitting form with status:', status); // Debug log

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
                        success: function (response) {
                            if (response.success) {
                                // Remove the processed row
                                row.fadeOut(300, function () {
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
                        error: function (xhr) {
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
                        complete: function () {
                            buttons.prop('disabled', false)
                                .html(function () {
                                    return $(this).hasClass('push-for-amendment-btn') ?
                                        '<i class="bx bx-check"></i> Push for Edit' :
                                        '<i class="bx bx-x"></i> Decline';
                                });
                        }
                    });
                }

                // Auto-resize textareas
                document.querySelectorAll('.editable-textarea').forEach(textarea => {
                    textarea.addEventListener('input', function () {
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
