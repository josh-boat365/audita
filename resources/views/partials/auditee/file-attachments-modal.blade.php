{{-- file-attachments-modal.blade.php  --}}
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
                                        <h4 style="margin-bottom: 10px;">Drop files here or click to upload.</h4>
                                        <span class="text-muted">Maximum file size: 5MB</span>
                                    </div>
                                    <!-- File preview area -->
                                    <div id="file-preview-{{ $exceptionItem->id }}" class="mt-3"></div>
                                </div>
                                <div class="mt-4 text-end">
                                    <!-- Made button ID unique and initially hidden -->
                                    <button type="button" id="upload-button-{{ $exceptionItem->id }}" class="btn btn-primary" style="display: none;">Upload
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
            initializeFileUpload{{ $exceptionItem->id }}();
        });

        function initializeFileUpload{{ $exceptionItem->id }}() {
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
                        icon: "warning",
                        title: "No Files",
                        text: "Please select files to upload first",
                        toast: true,
                        position: "top-end",
                        showConfirmButton: false,
                        timer: 3000
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
                    url: "{{ route('exception.file.upload', $exceptionItem->id) }}",
                    method: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        console.log("Upload success:", response);

                        // Reset button
                        uploadButton.innerHTML = 'Upload File';
                        uploadButton.disabled = false;
                        uploadButton.style.display = 'none';

                        if (response.status === "success") {
                            Swal.fire({
                                icon: "success",
                                title: "Success",
                                text: response.message || "Files uploaded successfully",
                                toast: true,
                                position: "top-end",
                                showConfirmButton: false,
                                timer: 3000
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
                                icon: "error",
                                title: "Error",
                                text: response.message || "Upload failed",
                                toast: true,
                                position: "top-end",
                                showConfirmButton: false,
                                timer: 3000
                            });
                        }
                    },
                    error: function(xhr) {
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
                            icon: "error",
                            title: "Upload Failed",
                            text: errorMessage,
                            toast: true,
                            position: "top-end",
                            showConfirmButton: false,
                            timer: 5000
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
                url: "{{ route('exception.file.download', ':id') }}".replace(':id', fileId),
                method: "GET",
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                },
                success: function(response) {
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
                            icon: "error",
                            title: "Download Failed",
                            text: response.message,
                            toast: true,
                            position: "top-end",
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Failed to download file. Please try again.",
                        toast: true,
                        position: "top-end",
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            });
        }

        // Delete file function
        function deleteFile(fileId) {
            Swal.fire({
                title: "Are you sure?",
                text: "This file will be permanently deleted!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('exception.file.delete', ':id') }}".replace(':id', fileId),
                        method: "DELETE",
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.status === "success") {
                                Swal.fire({
                                    icon: "success",
                                    title: "Deleted!",
                                    text: response.message,
                                    toast: true,
                                    position: "top-end",
                                    showConfirmButton: false,
                                    timer: 3000
                                });

                                // Remove file from UI with animation
                                $(`#file-${fileId}`).fadeOut(300, function() {
                                    $(this).remove();
                                });
                            } else {
                                Swal.fire({
                                    icon: "error",
                                    title: "Error",
                                    text: response.message,
                                    toast: true,
                                    position: "top-end",
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: "Failed to delete file. Please try again.",
                                toast: true,
                                position: "top-end",
                                showConfirmButton: false,
                                timer: 3000
                            });
                        }
                    });
                }
            });
        }
    </script>
@endpush
