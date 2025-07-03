<div class="card border-primary">
    <div class="card-header bg-light">
        <h6 class="mb-0">
            <i class="bx bx-cloud-upload me-2"></i>Upload New Files
        </h6>
    </div>
    <div class="card-body">
        <form id="file-upload-form" action="{{ route('exception.file.upload', $exceptionItem->id) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            <label class="form-label">Attach Files</label>
            <div class="dropzone file-upload-dropzone">
                <div class="dz-message needsclick">
                    <div class="mb-3">
                        <i class="display-4 text-muted bx bxs-cloud-upload"></i>
                    </div>
                    <h4>Drop files here or click to upload.</h4>
                </div>
            </div>
            <div class="mt-4 text-end">
                <button type="button" id="upload-button" class="btn btn-primary">Upload
                    File</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
    <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        Dropzone.autoDiscover = false;
        var myDropzone = new Dropzone(".file-upload-dropzone", {
            url: "{{ route('exception.file.upload', $exceptionItem->id) }}",
            paramName: "files[]",
            maxFilesize: 5,
            autoProcessQueue: false,
            addRemoveLinks: true,
            dictDefaultMessage: "Drop files here or click to upload",
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            init: function() {
                var submitButton = document.querySelector("#upload-button");
                var myDropzone = this;

                submitButton.addEventListener("click", function() {
                    myDropzone.processQueue();
                });

                this.on("success", function(file, response) {
                    if (response.status === "success") {
                        Swal.fire({
                            icon: "success",
                            title: "Success",
                            text: response.message,
                            toast: true,
                            position: "top-end",
                            showConfirmButton: false,
                            timer: 3000
                        });

                        //refresh page manually
                        window.location.reload();


                        {{--  fetchExceptionFiles(); // Reload files dynamically  --}}
                        myDropzone.removeFile(file);
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
                });

                this.on("error", function(file, response) {
                    Swal.fire({
                        icon: "error",
                        title: "Upload Failed",
                        text: response.message || "An error occurred",
                        toast: true,
                        position: "top-end",
                        showConfirmButton: false,
                        timer: 3000
                    });

                    file.previewElement.classList.add("dz-error");
                });
            }
        });
    </script>
@endpush
