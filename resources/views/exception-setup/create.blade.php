<x-base-layout>

    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Create New Exception</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <form action="{{ route('exception.post') }}" method="POST" enctype="multipart/form-data" autocomplete="on"
            class="needs-validation">
            @csrf
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">

                            <div class="mb-3">
                                <label class="form-label">Exception Title<span class="required">*</span></label>
                                <textarea class="form-control" rows="3"  name="title"
                                    placeholder="Enter exception title......" required>{{ old('title') }}</textarea>
                                <div class="invalid-feedback">Please enter exception title.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Exception Description<span class="required">*</span></label>
                                <textarea class="form-control" rows="3" id="exception_description" name="description"
                                    placeholder="Enter exception description......" required>{{ old('description') }}</textarea>
                                <div class="invalid-feedback">Please enter an exception description.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Root Cause</label>
                                <textarea class="form-control" rows="3" name="rootCause" placeholder="Enter root cause details......">{{ old('rootCause') }}</textarea>
                                <div class="invalid-feedback">Please enter the root cause.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Batch<span class="required">*</span></label>
                                <select class="form-select select2" name="exceptionBatchId" required>
                                    <option >Select.....</option>
                                    @foreach ($batches as $batch)
                                        <option value="{{ $batch->id }}"
                                            {{ $batch->id === old('exceptionBatchId') ? 'selected' : '' }}>
                                            {{ $batch->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Unit/Dept<span class="required">*</span></label>
                                <select class="form-select select2" name="departmentId" required>
                                    <option>Select Unit/Department</option>
                                    @foreach ($departments as $department)
                                        <option value="{{ $department->id }}"
                                            {{ $department->id === old('departmentId') ? 'selected' : '' }}>
                                            {{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Occurrence Date<span class="required">*</span></label>
                                <input type="text" class="form-control" placeholder="Select occurrence date"
                                    name="occurrenceDate" value="{{ old('occurrenceDate') }}"
                                    data-date-format="dd/mm/yyyy" data-provide="datepicker" data-date-autoclose="true"
                                    required />
                                <div class="invalid-feedback">Please select occurrence date.</div>
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

                            {{--  <div class="mb-3">
                                <label class="form-label" for="project-status-input">Status</label>
                                <select class="form-select" name="status" required>
                                    <option selected>Select.....</option>
                                    <option value="PENDING" @selected(old('status') === 'PENDING')>Pending</option>
                                    <option value="RESOLVED" @selected(old('status') === 'RESOLVED')>Resolved</option>
                                </select>
                                <div class="invalid-feedback">Please select exception status.</div>
                            </div>  --}}

                            <input type="hidden" name="status" value="PENDING">

                            <div class="mb-3">
                                <label class="form-label" for="project-visibility-input">Risk Rate</label>
                                <select class="form-select select2" name="riskRateId">
                                    {{--  <option value="">Select.....</option>  --}}
                                    @foreach ($riskRates as $riskRate)
                                        <option value="{{ $riskRate->id }}"
                                            {{ $riskRate->id === old('riskRateId') ? 'selected' : '' }}>
                                            {{ $riskRate->name }}</option>
                                    @endforeach

                                </select>
                            </div>

                            <div>
                                <label class="form-label" for="project-visibility-input">Process Type/Scope<span
                                        class="required">*</span></label>
                                <select class="form-select select2" name="processTypeId" required>
                                    <option >Select.....</option>
                                    @foreach ($processTypes as $processType)
                                        <option value="{{ $processType->id }}"
                                            {{ $processType->id === old('processTypeId') ? 'selected' : '' }}>
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
                                <label class="form-label">Proposed Resolution Date</label>
                                <input type="text" class="form-control" placeholder="Select due date"
                                    name="proposeResolutionDate" value="{{ old('proposeResolutionDate') }}"
                                    data-date-format="dd/mm/yyyy" data-provide="datepicker"
                                    data-date-autoclose="true" />
                                <div class="invalid-feedback">Please select proposed resolution date.</div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Resolution Date</label>
                                <input type="text" class="form-control" placeholder="Select resolution date"
                                    name="resolutionDate" value="{{ old('resolutionDate') }}"
                                    data-date-format="dd/mm/yyyy" data-provide="datepicker"
                                    data-date-autoclose="true" />
                                <div class="invalid-feedback">Please select resolution date.</div>
                            </div>
                        </div>
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


</x-base-layout>
