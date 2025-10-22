<x-base-layout>

    <div class="container-fluid px-1">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box ">
                    <h4 class="mb-sm-0 font-size-18"></h4>
                    <h1 class="mb-0">Auditor: Track Exception Statuses</h1>
                    <p class="text-muted mb-0">Keep track of all exceptions raised before pushed to branch for response.
                    </p>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="mt-4 mb-4" style="background-color: gray; height: 1px;"></div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Auditor</th>
                        <th>Branch</th>
                        <th>Department</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>

                    @forelse ($pendingExceptions as $exception)
                        <tr>
                            <th scope="row"><a href="#">{{ $exception['submittedBy'] }}</a></th>

                            <td>
                                <span class="dropdown badge rounded-pill bg-primary">
                                    {{ $exception['groupName'] }}
                                </span>
                            </td>
                            <td>
                                <div>
                                    {{ $exception['department'] }}
                                    <span class="dropdown badge rounded-pill bg-dark">
                                        {{ $exception['totalExceptionCount'] }}
                                    </span>
                                </div>
                                <div class="d-flex flex-column mt-1">
                                    <!-- Status Breakdown -->
                                    <div class="d-flex flex-wrap gap-1">
                                        @if ($exception['pendingCount'] > 0)
                                            <span class="badge badge-soft-warning">
                                                <b>{{ $exception['pendingCount'] }}</b> pending
                                            </span>
                                        @endif

                                        @if ($exception['notResolvedCount'] > 0)
                                            <span class="badge badge-soft-danger">
                                                <b>{{ $exception['notResolvedCount'] }}</b> not resolved at branch
                                            </span>
                                        @endif

                                        @if ($exception['resolvedCount'] > 0)
                                            <span class="badge badge-soft-info">
                                                <b>{{ $exception['resolvedCount'] }}</b> resolved at branch
                                            </span>
                                        @endif

                                        @if ($exception['approvedCount'] > 0)
                                            <span class="badge badge-soft-info">
                                                <b>{{ $exception['approvedCount'] }}</b> approved at supervisor
                                            </span>
                                        @endif

                                        @if ($exception['approvedCount'] > 0)
                                            <span class="badge badge-soft-success">
                                                <b>{{ $exception['approvedCount'] }}</b> approved
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Alternative: Combined Status Summary -->
                                    @if ($exception['pendingCount'] > 0 || $exception['notResolvedCount'] > 0)
                                        <p class="badge badge-soft-warning mt-1">
                                            <b>{{ $exception['pendingCount'] + $exception['notResolvedCount'] }}</b>
                                            require attention of <b>{{ $exception['totalExceptionCount'] }}</b> total
                                        </p>
                                    @endif

                                    <!-- Progress Information -->
                                    @if ($exception['completedCount'] > 0)
                                        <p class="badge badge-soft-success">
                                            <b>{{ $exception['completedCount'] }} completed</b>
                                            of <b>{{ $exception['totalExceptionCount'] }}</b> total
                                        </p>
                                    @endif
                                </div>
                            </td>
                            <td> {{ Carbon\Carbon::parse($exception['submittedAt'])->format('jS F, Y ') }} </td>
                            <td> <span
                                    class="dropdown badge rounded-pill bg-{{ $exception['status'] === 'PENDING' ? 'secondary' : ($exception['status'] === 'REVIEW' ? 'primary' : 'warning') }} ">
                                    {{ $exception['status'] }}
                                </span>
                            </td>

                            <td>
                                <div class="d-flex gap-3">
                                    <a
                                        href="{{ url("/exception/exception-status-view/{$exception['id']}/{$exception['status']}") }}">
                                        <span class="badge round bg-primary font-size-13"><i
                                                class="bx bxs-pencil"></i>open</span>
                                    </a>
                                </div>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bx bx-file fs-1 text-muted"></i>
                                <p class="mb-0">No pending exceptions for <b>RESPONSE</b> from <b>AUDITOR</b></p>
                                <small>All exceptions have been processed</small>
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>
    </div>



</x-base-layout>
