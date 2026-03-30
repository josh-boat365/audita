{{-- partials.auditee.exceptions-table --}}

@php
// Sort exceptions: non-RESOLVED items first, then RESOLVED items
$checkForNotResolved = 1;
$status = $pendingException->status;
$sortedExceptions = collect($exceptions ?? [])
    ->sortBy(function ($exception) use ($status) {
        // For ANALYSIS status, sort by exception status
        if ($status === 'ANALYSIS') {
            return $exception->status === 'RESOLVED' ? 1 : 0;
        }

        // For other statuses, sort by recommendedStatus
        return $exception->recommendedStatus === 'RESOLVED' ? 1 : 0;
    })
    ->values()
    ->all();
@endphp

{{--  {{ dd($sortedExceptions) }}  --}}

<div class="table-responsive">
    <table class="table table-bordered table-hover mb-0" id="exceptionsTable">
        <thead class="table-light">
            <tr>
                <th style="width: 50px">#</th>
                <th style="width: 200px">Exception Title</th>
                <th>Exception Description</th>
                {{--  <th scope="col">Sub Category</th>  --}}
                <th style="width: 300px">Management Response</th>
                <th style="width: 100px">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($sortedExceptions as $key => $exceptionItem)
                @if (!empty($exceptionItem))
                    @php
        // Determine row color class based on status
        $rowColorClass = '';

        if ($pendingException->status === 'ANALYSIS') {
            // For ANALYSIS status, check exception status
            if ($exceptionItem->status === 'RESOLVED') {
                $rowColorClass = 'table-success';
            } elseif ($exceptionItem->status === 'NOT-RESOLVED') {
                $rowColorClass = 'table-danger';
            }
        } else {
            // For non-ANALYSIS status, check recommendedStatus and status
            if ($exceptionItem->recommendedStatus === 'RESOLVED') {
                $rowColorClass = 'table-success';
            } elseif ($exceptionItem->status === 'NOT-RESOLVED') {
                $rowColorClass = 'table-danger';
            }
        }
                    @endphp

                    <tr data-exception-id="{{ $exceptionItem->id ?? '' }}" class="{{ $rowColorClass }}">
                        <input type="hidden" name="singleExceptionId" value="{{ $exceptionItem->id ?? '' }}">
                        <input type="hidden" name="status" id="status">

                        <!-- Row Number -->
                        <td>{{ ++$key }}</td>

                        <!-- Exception Title -->
                        <td>
                            <textarea @disabled($pendingException->status !== 'ANALYSIS') class="form-control editable-textarea" rows="3" name="exceptionTitle"
                                placeholder="Enter exception title">{{ $exceptionItem->exceptionTitle ?? '' }}</textarea>
                        </td>

                        <!-- Exception Description -->
                        <td>
                            <textarea @disabled($pendingException->status !== 'ANALYSIS') class="form-control editable-textarea" rows="3"
                                name="exceptions[{{ $exceptionItem->id ?? '' }}][exception]" placeholder="Enter exception description">{{ $exceptionItem->exception ?? '' }}</textarea>
                        </td>

                        <!-- Sub Category -->
                        {{--  <td>
                            <select @disabled($pendingException->status !== 'ANALYSIS') class="form-select sub-process-type"
                                name="exceptions[{{ $exceptionItem->id ?? '' }}][subProcessTypeId]">
                                <option value="">Select...</option>
                                @if (isset($pendingException->processTypeId) && isset($groupedSubProcessTypes[$pendingException->processTypeId]))
                                    @foreach ($groupedSubProcessTypes[$pendingException->processTypeId] as $subProcessType)
                                        @if (!empty($subProcessType))
                                            <option value="{{ $subProcessType->id }}" @selected(isset($exceptionItem->subProcessTypeId) && $subProcessType->id === $exceptionItem->subProcessTypeId)>
                                                {{ $subProcessType->name ?? '' }}
                                            </option>
                                        @endif
                                    @endforeach
                                @endif
                            </select>
                        </td>  --}}

                        <!-- Exception Response -->
                        <td>
                            <textarea class="form-control editable-textarea" rows="3"
                                name="exceptions[{{ $exceptionItem->id ?? '' }}][response]" placeholder="Enter exception response">{{ $exceptionItem->statusComment ?? '' }}</textarea>
                        </td>

                        <!-- Action Buttons -->
                        <td>
                            @include('partials.auditee.action-buttons', [
            'exceptionItem' => $exceptionItem,
            'pendingExceptionBatchStatus' => $pendingException->status,
            'pushBackButtonCheck' => $pushBackButtonCheck,
            'auditorButtonCheck' => $auditorButtonCheck,
            'employeeDepartmentId' => $employeeDepartmentId,
            'auditorDepartments' => $auditorDepartments,
            'pendingExceptionBatchStatusId' => $pendingException->id,
        ])
                        </td>
                    </tr>
                @endif
            @empty
                <tr>
                    <td colspan="6" class="text-center">No exceptions found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @php
// Check if all rows are green based on current batch status
// For ANALYSIS status: all must have status === 'RESOLVED'
// For other statuses: all must have recommendedStatus === 'RESOLVED'
$allResolved = collect($sortedExceptions)->every(function ($exception) use ($status) {
    if ($status === 'ANALYSIS') {
        return $exception->status === 'RESOLVED';
    }
    return $exception->recommendedStatus === 'RESOLVED';
});
    @endphp

    @if ($allResolved && $pendingException->status !== 'ANALYSIS')
        {{--  @if ($allResolved && $pendingException->status !== 'ANALYSIS')  --}}

        <div class="mt-3 mb-4 float-end">
            {{-- Action Button for Pushing to Resolved --}}
            <form class="exception-form" action="{{ route('exception.supervisor.action') }}" method="POST">
                @csrf
                <input type="hidden" name="batchExceptionId" value="{{ $pendingException->id ?? '' }}">
                <input type="hidden" name="status" value="ANALYSIS">
                <button type="submit" class="btn btn-success">
                    <i class="bx bx-analyse"></i> Push to Auditor Analysis
                </button>
            </form>
        </div>
    @elseif($allResolved && $pendingException->status === 'ANALYSIS' && in_array($employeeDepartmentId, $auditorDepartments))
        <div class="mt-3 mb-4 float-end">
            {{-- Action Button for Setting to Resolved - Only shown when in ANALYSIS status with all rows green --}}
            <form action="{{ route('exception.supervisor.action') }}" method="POST">
                @csrf
                <input type="hidden" name="batchExceptionId" value="{{ $pendingException->id ?? '' }}">
                <input type="hidden" name="status" value="RESOLVED">
                <button type="submit" class="btn btn-dark">
                    <i class="bx bx-analyse"></i> Set Batch to Resolved/Completed
                </button>
            </form>
        </div>
    @else
        <div></div>
    @endif

</div>
