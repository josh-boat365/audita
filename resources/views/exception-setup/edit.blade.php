<x-base-layout>

    <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18"> <a href="{{ route('exception.list') }}">
                            {{ $exception->exception }}</a> > Update Exception
                    </h4>
                </div>
            </div>
        </div>
        <!-- end page title -->


        <div class="mt-4 mb-4" style="background-color: gray; height: 1px;"></div>

        <form action="" method="POST" enctype="multipart/form-data" autocomplete="on" class="needs-validation">
            @csrf
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">

                            {{--  Project Image   --}}
                            <div class="mb-3">
                                <label class="form-label">Exception</label>
                                <textarea class="form-control" rows="3" name="exception" placeholder="Enter exception details......" required>{{ $exception->exception }}</textarea>
                                <div class="invalid-feedback">Please enter an exception.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Root Cause</label>
                                <textarea class="form-control" rows="3" name="rootCause" placeholder="Enter root cause details......" required>{{ $exception->rootCause }}</textarea>
                                <div class="invalid-feedback">Please enter the root cause.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Batch</label>
                                <select class="form-select select2" name="exceptionBatchId" required>
                                    <option selected>Select.....</option>
                                    @foreach ($batches as $batch)
                                        <option value="{{ $batch->id }}" @selected($batch->id === $exception->exceptionBatchId)>
                                            {{ $batch->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Unit/Dept</label>
                                <select class="form-select select2" name="departmentId" required>
                                    <option>Select Unit/Department</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}" @selected($department->id === $exception->departmentId)>
                                            {{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="form-label">Attached Files</label>
                                <div class="dropzone" id="myId">
                                    <div class="dz-message needsclick">
                                        <div class="mb-3">
                                            <i class="display-4 text-muted bx bxs-cloud-upload"></i>
                                        </div>
                                        <h4>Drop files here or click to upload.</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- end card body -->
                    </div>
                    <!-- end card -->

                </div>
                <!-- end col -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Actions</h5>

                            <div class="mb-3">
                                <label class="form-label" for="project-status-input">Status</label>
                                <select class="form-select" name="status" required>
                                    <option selected>Select.....</option>
                                    <option value="PENDING" @selected($exception->status === 'PENDING')>Pending</option>
                                    <option value="RESOLVED" @selected($exception->status === 'RESOLVED')>Resolved</option>
                                </select>
                                <div class="invalid-feedback">Please select exception status.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="project-visibility-input">Risk Rate</label>
                                <select class="form-select select2" name="riskRateId" required>
                                    <option selected>Select.....</option>
                                    @foreach ($riskRates as $riskRate)
                                        <option value="{{ $riskRate->id }}" @selected($riskRate->id === $exception->riskRateId)>
                                            {{ $riskRate->name }}</option>
                                    @endforeach

                                </select>
                            </div>

                            <div>
                                <label class="form-label" for="project-visibility-input">Process Type/Scope</label>
                                <select class="form-select select2" name="processTypeId" required>
                                    <option selected>Select.....</option>
                                    @foreach ($processTypes as $processType)
                                        <option value="{{ $processType->id }}" @selected($processType->id === $exception->processTypeId)>
                                            {{ $processType->name }}</option>
                                    @endforeach
                                </select>
                            </div>


                        </div>
                        <!-- end card body -->
                    </div>
                    <!-- end card -->

                    <div class="card">
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">Occurrence Date</label>
                                <input type="text"  class="form-control"
                                    placeholder="Select occurrence date" name="occurrenceDate"
                                    value="{{ $exception->occurrenceDate == null ? '' :  Carbon\Carbon::parse($exception->occurrenceDate)->format('jS F, Y ') }}" data-date-format="dd/mm/yyyy"
                                    data-provide="datepicker" data-date-autoclose="true" required />
                                <div class="invalid-feedback">Please select occurrence date.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Proposed Resolution Date</label>
                                <input type="text"  class="form-control"
                                    placeholder="Select due date" name="proposeResolutionDate"
                                    value="{{ $exception->proposeResolutionDate == null ? '' : Carbon\Carbon::parse($exception->proposeResolutionDate)->format('jS F, Y ')}}" data-date-format="dd/mm/yyyy"
                                    data-provide="datepicker" data-date-autoclose="true" />
                                <div class="invalid-feedback">Please select proposed resolution date.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Resolution Date</label>
                                <input type="text" class="form-control"
                                    placeholder="Select resolution date" name="resolutionDate"
                                    value="{{ $exception->resolutionDate == null ? '' :  Carbon\Carbon::parse($exception->resolutionDate)->format('jS F, Y ')  }}" data-date-format="dd/mm/yyyy"
                                    data-provide="datepicker" data-date-autoclose="true" />
                                <div class="invalid-feedback">Please select resolution date.</div>
                            </div>
                        </div>
                        <!-- end card body -->
                    </div>
                    <!-- end card -->
                </div>
                <!-- end col -->

                <div class="col-lg-8">
                    <div class="text-end mb-4">
                        <button type="submit" class="btn btn-primary">Create Exception</button>
                    </div>
                </div>
            </div>
            <!-- end row -->
        </form>

    </div>
    <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
    <script>
        Dropzone.autoDiscover = false;
        var myDropzone = new Dropzone("#myId", {
            url: "/file/post", // Set the url for your upload script
            paramName: "file", // The name that will be used to transfer the file
            maxFilesize: 2, // MB
            addRemoveLinks: true,
            dictDefaultMessage: "Drop files here or click to upload",
            init: function() {
                this.on("success", function(file, response) {
                    console.log("File uploaded successfully");
                });
                this.on("error", function(file, response) {
                    console.log("File upload failed");
                });
            }
        });
    </script>
</x-base-layout>
