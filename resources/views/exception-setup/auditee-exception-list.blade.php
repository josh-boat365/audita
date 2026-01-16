<x-base-layout>

    <div class="container-fluid px-1">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="mb-sm-0 font-size-18"></h4>
                    <h1 class="mb-0">Audit Control Exceptions For Branch</h1>
                    <p class="text-muted mb-0">Respond to all exceptions raised and push them to the audit control
                        officer for analysis.</p>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <!-- Summary Cards -->
        @php
            $exceptions = collect($pendingExceptions);
            $totalExceptions = $exceptions->sum('exceptionCount');
            $totalResponded = $exceptions->sum('countForRespondedExceptionsByAuditee');
            $totalBatches = $exceptions->count();
            $responseRate = $totalExceptions > 0 ? round(($totalResponded / $totalExceptions) * 100, 1) : 0;

            // Count by status
            $pendingApprovalCount = $exceptions->where('status', 'APPROVED')->sum('exceptionCount');
            $otherStatusCount = $exceptions->where('status', '!=', 'APPROVED')->sum('exceptionCount');
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
                                <p class="text-muted fw-medium mb-2">Responded</p>
                                <h4 class="mb-0 text-success" id="totalResponded">{{ $totalResponded }}</h4>
                            </div>
                            <div class="avatar-sm rounded-circle bg-success align-self-center mini-stat-icon">
                                <span class="avatar-title rounded-circle bg-success">
                                    <i class="bx bx-check-double font-size-24"></i>
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
                                <p class="text-muted fw-medium mb-2">Pending Approval</p>
                                <h4 class="mb-0 text-warning" id="pendingApproval">{{ $pendingApprovalCount }}</h4>
                            </div>
                            <div class="avatar-sm rounded-circle bg-warning align-self-center mini-stat-icon">
                                <span class="avatar-title rounded-circle bg-warning">
                                    <i class="bx bx-time-five font-size-24"></i>
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
                                <p class="text-muted fw-medium mb-2">Response Rate</p>
                                <h4 class="mb-0 text-info" id="responseRate">{{ $responseRate }}%</h4>
                            </div>
                            <div class="avatar-sm rounded-circle bg-info align-self-center mini-stat-icon">
                                <span class="avatar-title rounded-circle bg-info">
                                    <i class="bx bx-trending-up font-size-24"></i>
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
            $uniqueDepartments = collect($pendingExceptions)->pluck('department')->unique()->sort()->values();
        @endphp

        <div class="row mb-3">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="bx bx-filter-alt"></i> Filters
                        </h5>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="auditorFilter" class="form-label">Filter by Auditor</label>
                                <select id="auditorFilter" class="form-select">
                                    <option value="">All Auditors</option>
                                    @foreach($uniqueAuditors as $auditor)
                                        <option value="{{ $auditor }}">{{ $auditor }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="statusFilter" class="form-label">Filter by Status</label>
                                <select id="statusFilter" class="form-select">
                                    <option value="">All Statuses</option>
                                    @foreach($uniqueStatuses as $status)
                                        <option value="{{ $status }}">
                                            {{ $status === 'APPROVED' ? 'PENDING AUDITOR APPROVAL' : $status }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="branchFilter" class="form-label">Filter by Branch</label>
                                <select id="branchFilter" class="form-select">
                                    <option value="">All Branches</option>
                                    @foreach($uniqueBranches as $branch)
                                        <option value="{{ $branch }}">{{ $branch }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="departmentFilter" class="form-label">Filter by Department</label>
                                <select id="departmentFilter" class="form-select">
                                    <option value="">All Departments</option>
                                    @foreach($uniqueDepartments as $department)
                                        <option value="{{ $department }}">{{ $department }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12 d-flex gap-2">
                                <button type="button" id="clearFilters" class="btn btn-secondary btn-sm">
                                    <i class="bx bx-reset"></i> Clear Filters
                                </button>
                                <span class="text-muted align-self-center ms-2">
                                    <small>Showing <strong id="visibleCount">{{ $totalBatches }}</strong> of {{ $totalBatches }} batches</small>
                                </span>
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
                        <tr data-auditor="{{ $exception['submittedBy'] }}"
                            data-status="{{ $exception['status'] }}"
                            data-branch="{{ $exception['groupName'] }}"
                            data-department="{{ $exception['department'] }}"
                            data-exception-count="{{ $exception['exceptionCount'] }}"
                            data-responded-count="{{ $exception['countForRespondedExceptionsByAuditee'] }}">
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
                                        {{ $exception['exceptionCount'] }}
                                    </span>
                                </div>
                                <div class="">
                                    <p class="badge badge-soft-secondary">
                                        <b>{{ $exception['countForRespondedExceptionsByAuditee'] }}</b> responded
                                        exception(s) of <b>{{ $exception['exceptionCount'] }}</b> total exception(s)
                                    </p>
                                    <br>
                                </div>
                            </td>
                            <td>{{ Carbon\Carbon::parse($exception['submittedAt'])->format('jS F, Y ') }}</td>
                            <td>
                                <span class="dropdown badge rounded-pill bg-success">
                                    {{ $exception['status'] === 'APPROVED' ? 'PENDING AUDITOR APPROVAL' : $exception['status'] }}
                                </span>
                            </td>

                            <td>
                                <div class="d-flex gap-3">
                                    <a href="{{ url("/exception/supervisor/show-exception-list-for-approval/{$exception['id']}/{$exception['status']}") }}">
                                        <span class="badge round bg-primary font-size-13">
                                            <i class="bx bxs-pencil"></i>open
                                        </span>
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
                const departmentFilter = document.getElementById('departmentFilter');
                const clearFiltersBtn = document.getElementById('clearFilters');
                const tableRows = document.querySelectorAll('#exceptionsTable tbody tr:not(#emptyState)');
                const totalBatchesOriginal = {{ $totalBatches }};

                function filterTable() {
                    const selectedAuditor = auditorFilter.value.toLowerCase();
                    const selectedStatus = statusFilter.value.toLowerCase();
                    const selectedBranch = branchFilter.value.toLowerCase();
                    const selectedDepartment = departmentFilter.value.toLowerCase();

                    let visibleCount = 0;

                    tableRows.forEach(row => {
                        const auditor = row.getAttribute('data-auditor').toLowerCase();
                        const status = row.getAttribute('data-status').toLowerCase();
                        const branch = row.getAttribute('data-branch').toLowerCase();
                        const department = row.getAttribute('data-department').toLowerCase();

                        const auditorMatch = !selectedAuditor || auditor === selectedAuditor;
                        const statusMatch = !selectedStatus || status === selectedStatus;
                        const branchMatch = !selectedBranch || branch === selectedBranch;
                        const departmentMatch = !selectedDepartment || department === selectedDepartment;

                        if (auditorMatch && statusMatch && branchMatch && departmentMatch) {
                            row.style.display = '';
                            visibleCount++;
                        } else {
                            row.style.display = 'none';
                        }
                    });

                    // Update visible count display
                    document.getElementById('visibleCount').textContent = visibleCount;

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
                    let totalResponded = 0;
                    let pendingApproval = 0;

                    tableRows.forEach(row => {
                        if (row.style.display !== 'none') {
                            const exceptionCount = parseInt(row.getAttribute('data-exception-count')) || 0;
                            const respondedCount = parseInt(row.getAttribute('data-responded-count')) || 0;
                            const status = row.getAttribute('data-status');

                            totalExceptions += exceptionCount;
                            totalResponded += respondedCount;

                            // Count pending approval (APPROVED status means pending auditor approval)
                            if (status === 'APPROVED') {
                                pendingApproval += exceptionCount;
                            }
                        }
                    });

                    // Calculate response rate
                    const responseRate = totalExceptions > 0
                        ? ((totalResponded / totalExceptions) * 100).toFixed(1)
                        : 0;

                    // Update the summary card values
                    document.getElementById('totalExceptions').textContent = totalExceptions;
                    document.getElementById('totalResponded').textContent = totalResponded;
                    document.getElementById('pendingApproval').textContent = pendingApproval;
                    document.getElementById('responseRate').textContent = responseRate + '%';
                }

                function clearFilters() {
                    auditorFilter.value = '';
                    statusFilter.value = '';
                    branchFilter.value = '';
                    departmentFilter.value = '';
                    filterTable();
                }

                // Event listeners
                auditorFilter.addEventListener('change', filterTable);
                statusFilter.addEventListener('change', filterTable);
                branchFilter.addEventListener('change', filterTable);
                departmentFilter.addEventListener('change', filterTable);
                clearFiltersBtn.addEventListener('click', clearFilters);
            });
        </script>
    @endpush

</x-base-layout>
