<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <div class="row align-items-center g-2">
                    <!-- Batch Filter -->
                    <div class="col-md-3">
                        <label class="form-label">Batch</label>
                        <select @disabled($pendingExceptionBatchStatus !== 'ANALYSIS') class="form-select select2" id="batchFilter">
                            <option>Select.....</option>
                            @foreach ($batches as $batch)
                                <option value="{{ $batch->id }}" @selected($batch->id === $batchId)>
                                    {{ $batch->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Process Types Filter -->
                    <div class="col-md-3">
                        <label class="form-label">Process Types</label>
                        <select @disabled($pendingExceptionBatchStatus !== 'ANALYSIS') class="form-select select2" id="processTypeFilter">
                            <option>Select.....</option>
                            @foreach ($processTypes as $processType)
                                <option value="{{ $processType->id }}" @selected($processType->id === $processTypeId)>
                                    {{ $processType->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Department Filter -->
                    <div class="col-md-3">
                        <label class="form-label">Department</label>
                        <select @disabled($pendingExceptionBatchStatus !== 'ANALYSIS') class="form-select select2" id="departmentFilter">
                            <option>Select.....</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}" @selected($department->id === $departmentId)>
                                    {{ $department->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date Filter -->
                    <div class="col-md-3">
                        <label class="form-label">Occurrence Date</label>
                        <input @disabled($pendingExceptionBatchStatus !== 'ANALYSIS') type="date" value="{{ $requestDate }}"
                            class="form-control" id="occurrenceDateFilter">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
