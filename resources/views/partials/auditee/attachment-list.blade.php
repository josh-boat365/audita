@forelse($attachments as $file)
    <div class="col-xl-4 mb-3" id="file-{{ $file->id }}">

        <div class="file-info p-3 border rounded">
            <p><strong>{{ $file->fileName }}</strong></p>
            <p class="text-muted">{{ $file->uploadDate }}</p>
            <p class="text-muted"><strong>Uploaded By: </strong>{{ $file->uploadedBy }}
            </p>

            <div class="d-flex justify-content-between">
                <!-- Download Button -->
                <button onclick="downloadFile('{{ $file->id }}')" class="btn btn-sm btn-primary">
                    <i class="fas fa-download"></i> Download
                </button>

                <!-- Delete Button -->
                @if ($file->uploadedBy == $employeeName)
                    <button onclick="deleteFile('{{ $file->id }}')" class="btn btn-danger btn-sm">
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

@push('scripts')
    <script>
        // Fetch and download files
        function downloadFile(fileId) {
            $.ajax({
                url: "{{ route('exception.file.download', ':id') }}".replace(':id', fileId),
                method: "GET",
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") // Include CSRF token
                },
                success: function(response) {
                    {{--  console.log("Download response:", response); // Debugging  --}}

                    if (response.status === "success") {
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
                    {{--  console.error("Download Error:", xhr.responseText);  --}}
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


        //Delete exception files
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
                        url: "{{ route('exception.file.delete', ':id') }}".replace(':id',
                            fileId),
                        method: "DELETE", // Changed to DELETE
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}" // CSRF token added
                        },
                        success: function(response) {
                            {{--  console.log("Delete Success:", response); // Debugging log  --}}

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

                                // Remove file from UI
                                document.querySelector(`#file-${fileId}`).remove();
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
                            {{--  console.error("Delete Error:", xhr.responseText); // Log error for debugging  --}}

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
    </script>
@endpush
