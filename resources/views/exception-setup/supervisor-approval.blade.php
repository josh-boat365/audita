<x-base-layout>

    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18"> <a href="{{ route('exception.list') }}">
                            Approve Exception </a> > Teller Operations
                    </h4>

                </div>
            </div>
        </div>
        <!-- end page title -->


        <div class="mt-4 mb-4" style="background-color: gray; height: 1px;"></div>



        <form action="{{ route('exception.update', $exception->id) }}" method="POST" enctype="multipart/form-data"
            autocomplete="on" class="needs-validation">
            @csrf
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">

                            <div class="mb-3">
                                <label class="form-label">Exception Title<span class="required">*</span></label>
                                <textarea class="form-control" rows="3" name="title" placeholder="Enter exception title......" required>{{ old('title') }}</textarea>
                                <div class="invalid-feedback">Please enter exception title.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Exception Description<span class="required">*</span></label>
                                <textarea class="form-control" rows="3" id="exception_description" name="description"
                                    placeholder="Enter exception description......" required>{{ $exception->exception }}</textarea>
                                <div class="invalid-feedback">Please enter an exception description.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Batch<span class="required">*</span></label>
                                <select class="form-select select2" name="exceptionBatchId" required>
                                    <option>Select.....</option>
                                    @foreach ($batches as $batch)
                                        <option value="{{ $batch->id }}" @selected($batch->id === $exception->exceptionBatchId)>
                                            {{ $batch->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Unit/Dept<span class="required">*</span></label>
                                <select class="form-select select2" name="departmentId" required>
                                    <option>Select Unit/Department</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}" @selected($department->id === $exception->departmentId)>
                                            {{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Occurrence Date<span class="required">*</span></label>
                                <input type="text" class="form-control" placeholder="Select occurrence date"
                                    name="occurrenceDate"
                                    value="{{ $exception->occurrenceDate == null ? '' : Carbon\Carbon::parse($exception->occurrenceDate)->format('d/m/Y') }}"
                                    data-date-format="d/m/yy" data-provide="datepicker" data-date-autoclose="true"
                                    required />
                                <div class="invalid-feedback">Please select occurrence date.</div>
                            </div>


                            <div class="mb-3">
                                <label class="form-label" for="project-visibility-input">Process
                                    Type/Scope<span class="required">*</span></label>
                                <select class="form-select select2" name="processTypeId" required>
                                    <option>Select.....</option>
                                    @foreach ($processTypes as $processType)
                                        <option value="{{ $processType->id }}" @selected($processType->id === $exception->processTypeId)>
                                            {{ $processType->name }}</option>
                                    @endforeach
                                </select>
                            </div>


                            <div class="mb-3">
                                <label class="form-label">Supervisor Comment<span class="required">*</span></label>
                                <textarea class="form-control" rows="3" name="title" placeholder="Enter comment here......" required>{{ old('title') }}</textarea>
                                <div class="invalid-feedback">Please enter exception title.</div>
                            </div>
                        </div>
                        <!-- end card body -->
                    </div>
                    <!-- end card -->

                </div>
                <!-- end col -->

                <div class="d-flex justify-content-end gap-3">
                    <div class="text-end mb-4">
                        <button type="submit" class="btn btn-success">Approve Exception</button>
                    </div>
                    <div class="text-end mb-4">
                        <button type="submit" data-bs-toggle="modal" data-bs-target=".bs-delete-modal-lg"
                            class="btn btn-danger">Probe Exception</button>
                    </div>

                    {{--  <a href="" data-bs-toggle="modal" data-bs-target=".bs-delete-modal-lg">
                        <span class="badge round bg-danger font-size-13"><i class="bx bxs-trash"></i> delete</span>
                    </a>  --}}
                    <!-- Modal for Delete Confirmation -->
                    <div class="modal fade bs-delete-modal-lg" tabindex="-1" role="dialog"
                        aria-labelledby="myLargeModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="myLargeModalLabel">Confirm</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <h4 class="text-center mb-4">Are you sure you want to push this exception for correction?</h4>
                                    {{--  <p class="text-center">Deleting a <b>batch</b> means removing it
                                        from the <b>system entirely</b> and you cannot
                                        <b>recover</b> it
                                        again
                                    </p>  --}}
                                    <form action="#" method="POST">
                                        @csrf

                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-danger">Yes,
                                                Probe</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end row -->
        </form>

        <!-- end card -->



    </div>


</x-base-layout>
