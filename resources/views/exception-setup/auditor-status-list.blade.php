<x-base-layout>

    <div class="container-fluid px-1">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="mb-sm-0 font-size-18"></h4>
                    <h1 class="mb-0">Auditor: Track Exception Statuses</h1>
                    <p class="text-muted mb-0">Keep track of all exceptions raised before pushed to branch for response.
                    </p>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <!-- Summary Cards -->
        @php
            $exceptions = collect($pendingExceptions);
            $totalExceptions = $exceptions->sum('totalExceptionCount');
            $totalPending = $exceptions->sum('pendingCount');
            $totalNotResolved = $exceptions->sum('notResolvedCount');
            $totalCompleted = $exceptions->sum('completedCount');
        @endphp

        <div class="row mt-4">
            <div class="col-xl-3 col-md-6">
                <div class="card mini-stats-wid">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium mb-2">Total Exceptions</p>
                                <h4 class="mb-0" id="totalExceptions">{{ $totalExceptions }}</h4>
                            </div>
                            <div class="avatar-sm rounded-circle bg-primary align-self-center mini-stat-icon">
                                <span class="avatar-title rounded-circle bg-primary">
                                    <i class="bx bx-error font-size-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card mini-stats-wid">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium mb-2">Pending</p>
                                <h4 class="mb-0 text-warning" id="totalPending">{{ $totalPending }}</h4>
                            </div>
                            <div class="avatar-sm rounded-circle bg-warning align-self-center mini-stat-icon">
                                <span class="avatar-title rounded-circle bg-warning">
                                    <i class="bx bx-time font-size-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card mini-stats-wid">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium mb-2">Not Resolved</p>
                                <h4 class="mb-0 text-danger" id="totalNotResolved">{{ $totalNotResolved }}</h4>
                            </div>
                            <div class="avatar-sm rounded-circle bg-danger align-self-center mini-stat-icon">
                                <span class="avatar-title rounded-circle bg-danger">
                                    <i class="bx bx-x-circle font-size-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card mini-stats-wid">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium mb-2">Completed</p>
                                <h4 class="mb-0 text-success" id="totalCompleted">{{ $totalCompleted }}</h4>
                            </div>
                            <div class="avatar-sm rounded-circle bg-success align-self-center mini-stat-icon">
                                <span class="avatar-title rounded-circle bg-success">
                                    <i class="bx bx-check-circle font-size-24"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 mb-4" style="background-color: gray; height: 1px;"></div>

        <!-- Filters Section -->
        @php
            $uniqueAuditors = collect($pendingExceptions)->pluck('submittedBy')->unique()->sort()->values();
            $uniqueStatuses = collect($pendingExceptions)->pluck('status')->unique()->sort()->values();
            $uniqueBranches = collect($pendingExceptions)->pluck('groupName')->unique()->sort()->values();
        @endphp

        <div class="row mb-3">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Filters</h5>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="auditorFilter" class="form-label">Filter by Auditor</label>
                                <select id="auditorFilter" class="form-select">
                                    <option value="">All Auditors</option>
                                    @foreach($uniqueAuditors as $auditor)
                                        <option value="{{ $auditor }}">{{ $auditor }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="statusFilter" class="form-label">Filter by Status</label>
                                <select id="statusFilter" class="form-select">
                                    <option value="">All Statuses</option>
                                    @foreach($uniqueStatuses as $status)
                                        <option value="{{ $status }}">{{ $status }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="branchFilter" class="form-label">Filter by Branch</label>
                                <select id="branchFilter" class="form-select">
                                    <option value="">All Branches</option>
                                    @foreach($uniqueBranches as $branch)
                                        <option value="{{ $branch }}">{{ $branch }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <button type="button" id="clearFilters" class="btn btn-secondary btn-sm">
                                    <i class="bx bx-reset"></i> Clear Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0" id="exceptionsTable">
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
                        <tr data-auditor="{{ $exception['submittedBy'] }}" data-status="{{ $exception['status'] }}"
                            data-branch="{{ $exception['groupName'] }}">
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
                        <tr id="emptyState">
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

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const auditorFilter = document.getElementById('auditorFilter');
                const statusFilter = document.getElementById('statusFilter');
                const branchFilter = document.getElementById('branchFilter');
                const clearFiltersBtn = document.getElementById('clearFilters');
                const tableRows = document.querySelectorAll('#exceptionsTable tbody tr:not(#emptyState)');

                function filterTable() {
                    const selectedAuditor = auditorFilter.value.toLowerCase();
                    const selectedStatus = statusFilter.value.toLowerCase();
                    const selectedBranch = branchFilter.value.toLowerCase();

                    let visibleCount = 0;

                    tableRows.forEach(row => {
                        const auditor = row.getAttribute('data-auditor').toLowerCase();
                        const status = row.getAttribute('data-status').toLowerCase();
                        const branch = row.getAttribute('data-branch').toLowerCase();

                        const auditorMatch = !selectedAuditor || auditor === selectedAuditor;
                        const statusMatch = !selectedStatus || status === selectedStatus;
                        const branchMatch = !selectedBranch || branch === selectedBranch;

                        if (auditorMatch && statusMatch && branchMatch) {
                            row.style.display = '';
                            visibleCount++;
                        } else {
                            row.style.display = 'none';
                        }
                    });

                    // Show/hide empty state
                    const emptyState = document.getElementById('emptyState');
                    if (emptyState) {
                        emptyState.style.display = visibleCount === 0 ? '' : 'none';
                    }

                    // Update summary cards based on visible rows
                    updateSummaryCards();
                }

                function updateSummaryCards() {
                    let totalExceptions = 0;
                    let totalPending = 0;
                    let totalNotResolved = 0;
                    let totalCompleted = 0;

                    tableRows.forEach(row => {
                        if (row.style.display !== 'none') {
                            // Extract counts from the row (you may need to adjust selectors)
                            const badges = row.querySelectorAll('.badge');
                            badges.forEach(badge => {
                                const text = badge.textContent;
                                if (text.includes('pending')) {
                                    const count = parseInt(text.match(/\d+/)?.[0] || 0);
                                    totalPending += count;
                                }
                                if (text.includes('not resolved')) {
                                    const count = parseInt(text.match(/\d+/)?.[0] || 0);
                                    totalNotResolved += count;
                                }
                                if (text.includes('completed')) {
                                    const count = parseInt(text.match(/\d+/)?.[0] || 0);
                                    totalCompleted += count;
                                }
                            });
                        }
                    });

                    // Update the summary card values
                    document.getElementById('totalPending').textContent = totalPending;
                    document.getElementById('totalNotResolved').textContent = totalNotResolved;
                    document.getElementById('totalCompleted').textContent = totalCompleted;
                }

                function clearFilters() {
                    auditorFilter.value = '';
                    statusFilter.value = '';
                    branchFilter.value = '';
                    filterTable();
                }

                // Event listeners
                auditorFilter.addEventListener('change', filterTable);
                statusFilter.addEventListener('change', filterTable);
                branchFilter.addEventListener('change', filterTable);
                clearFiltersBtn.addEventListener('click', clearFilters);
            });
        </script>
    @endpush

</x-base-layout>
