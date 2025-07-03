{{-- partials.auditee.exceptions-table --}}

@php
    // Sort exceptions: non-RESOLVED items first, then RESOLVED items

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

<div class="table-responsive">
    <table class="table table-bordered table-hover mb-0" id="exceptionsTable">
        <thead class="table-light">
            <tr>
                <th scope="col">#</th>
                <th scope="col">Exception Title</th>
                <th scope="col">Exception Description</th>
                <th scope="col">Sub Category</th>
                <th scope="col">Exception Response</th>
                <th scope="col">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($sortedExceptions as $key => $exceptionItem)
                @if (!empty($exceptionItem))
                    <tr data-exception-id="{{ $exceptionItem->id ?? '' }}"
                        class="{{
                            $pendingException->status === 'ANALYSIS'
                                ? ($exceptionItem->status === 'RESOLVED' ? 'table-success' : '')
                                : ($exceptionItem->recommendedStatus === 'RESOLVED' ? 'table-success' : '')
                        }}">
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
                        <td>
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
                        </td>

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
</div>
