{{--  partials.auditee.view-exception-modal  --}}
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
                <form action="{{ route('exception.update', $exceptionItem->id) }}" method="POST"
                    enctype="multipart/form-data" autocomplete="on" class="needs-validation">
                    @csrf
                    <input type="hidden" name="requestType" value="BATCH">
                    <input type="hidden" name="requestTrackerId" value="{{ $pendingException->id ?? '' }}">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-body">

                                    <div class="mb-3">
                                        <label class="form-label">Exception Title<span class="required">*</span></label>
                                        <textarea class="form-control" rows="3" name="exceptionTitle" placeholder="Enter exception details......"
                                            required>{{ $exceptionItem->exceptionTitle }}</textarea>
                                        <div class="invalid-feedback">Please enter an exception.</div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Exception Description<span
                                                class="required">*</span></label>
                                        <textarea class="form-control" rows="3" name="exception" placeholder="Enter exception details......" required>{{ $exceptionItem->exception }}</textarea>
                                        <div class="invalid-feedback">Please enter an exception.</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Root Cause</label>
                                        <textarea class="form-control" rows="3" name="rootCause" placeholder="Enter root cause details......">{{ $exceptionItem->rootCause }}</textarea>
                                        <div class="invalid-feedback">Please enter the root cause.</div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Branch/Auditee Response</label>
                                        <textarea class="form-control editable-textarea" rows="3" name="statusComment"
                                            placeholder="Enter exception response">{{ $exceptionItem->statusComment ?? '' }}</textarea>
                                    </div>



                                    <div class="mb-3">
                                        <label class="form-label">Unit/Dept<span class="required">*</span></label>
                                        <select class="form-select " name="departmentId" required>
                                            <option>Select Unit/Department</option>
                                            @foreach ($departments as $department)
                                                <option value="{{ $department->id }}" @selected($department->id === $exceptionItem->departmentId)>
                                                    {{ $department->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Occurrence Date<span class="required">*</span></label>
                                        <input type="date" class="form-control" name="occurrenceDate" required
                                            value="{{ $exceptionItem->occurrenceDate ? \Carbon\Carbon::parse($exceptionItem->occurrenceDate)->format('Y-m-d') : '' }}"
                                            placeholder="Select resolution date" />
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

                                    <div class="mb-3">
                                        <label class="form-label" for="project-status-input">Status</label>
                                        <select class="form-select" name="status">
                                            <option value="">Select.....</option>
                                            <option value="APPROVED" @selected($exceptionItem->status === 'APPROVED')>Approved</option>
                                            <option value="RESOLVED" @selected($exceptionItem->status === 'RESOLVED')>Resolved</option>
                                        </select>
                                        <div class="invalid-feedback">Please select exception status.</div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="project-visibility-input">Risk Rate</label>
                                        <select class="form-select" name="riskRateId">
                                            <option value="">Select.....</option>
                                            @foreach ($riskRates as $riskRate)
                                                <option value="{{ $riskRate->id }}" @selected($riskRate->id === $exceptionItem->riskRateId)>
                                                    {{ $riskRate->name }}</option>
                                            @endforeach

                                        </select>
                                    </div>
                                    {{--  <input type="hidden" name="status" value="RESOLVED">  --}}
                                    <div class="mb-3">
                                        <label class="form-label">Batch<span class="required">*</span></label>
                                        <select class="form-select " name="exceptionBatchId" required>
                                            <option>Select.....</option>
                                            @foreach ($batches as $batch)
                                                <option value="{{ $batch->id }}" @selected($batch->id === $exceptionItem->exceptionBatchId)>
                                                    {{ $batch->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>



                                    <div class="mb-3">
                                        <label class="form-label" for="project-visibility-input">Process
                                            Type/Scope<span class="required">*</span></label>
                                        <select class="form-select " name="processTypeId" required>
                                            <option>Select.....</option>
                                            @foreach ($processTypes as $processType)
                                                <option value="{{ $processType->id }}" @selected($processType->id === $exceptionItem->processTypeId)>
                                                    {{ $processType->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="project-visibility-input">Sub Process
                                            Type/Scope<span class="required">*</span></label>
                                        <select class="form-select sub-process-type" name="subProcessTypeId">
                                            <option value="">Select...</option>
                                            @if (isset($pendingException->processTypeId) && isset($groupedSubProcessTypes[$pendingException->processTypeId]))
                                                @foreach ($groupedSubProcessTypes[$pendingException->processTypeId] as $subProcessType)
                                                    @if (!empty($subProcessType))
                                                        <option value="{{ $subProcessType->id }}"
                                                            @selected(isset($exceptionItem->subProcessTypeId) && $subProcessType->id === $exceptionItem->subProcessTypeId)>
                                                            {{ $subProcessType->name ?? '' }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            @endif
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
                                        <small>(optional)</small>
                                        <input type="date" class="form-control" name="proposeResolutionDate"
                                            value="{{ $exceptionItem->proposeResolutionDate ? \Carbon\Carbon::parse($exceptionItem->proposeResolutionDate)->format('Y-m-d') : '' }}"
                                            placeholder="Select resolution date" />
                                        <div class="invalid-feedback">Please select proposed Resolution Date date.
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Resolution Date</label>
                                        <small>(optional)</small>

                                        <input type="date" class="form-control" name="resolutionDate"
                                            value="{{ $exceptionItem->resolutionDate ? \Carbon\Carbon::parse($exceptionItem->resolutionDate)->format('Y-m-d') : '' }}"
                                            placeholder="Select resolution date" />
                                        <div class="invalid-feedback">Please select resolution date.</div>
                                    </div>
                                </div>
                                <!-- end card body -->
                            </div>
                            <!-- end card -->
                        </div>
                        <!-- end col -->


                        <div class="d-grid text-end mb-4">
                            <button type="submit" class="btn btn-primary">Update Exception</button>
                        </div>

                    </div>
                    <!-- end row -->
                </form>

            </div>
        </div>
    </div>
</div>
