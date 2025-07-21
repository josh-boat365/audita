<x-base-layout>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <div class="container-fluid px-1">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Overview of Exception Reports For You</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 mb-4" style="background-color: gray; height: 1px;"></div>

    <!-- Filter Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">
                        <i class="fas fa-filter me-2"></i>Report Filters
                    </h4>
                    <form id="reportFilters">
                        <div class="row">
                            <div class="col-md-2">
                                <label for="auditorFilter" class="form-label">Auditor</label>
                                <select id="auditorFilter" class="form-select">
                                    <option value="">All Auditors</option>
                                    @foreach (array_unique(array_column($reports, 'auditorName')) as $auditor)
                                        @if ($auditor)
                                            <option value="{{ $auditor }}">{{ $auditor }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="statusFilter" class="form-label">Status</label>
                                <select id="statusFilter" class="form-select">
                                    <option value="">All Statuses</option>
                                    @foreach (array_unique(array_column($reports, 'status')) as $status)
                                        @if ($status)
                                            <option value="{{ $status }}">{{ $status }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="riskRateFilter" class="form-label">Risk Rate</label>
                                <select id="riskRateFilter" class="form-select">
                                    <option value="">All Risk Rates</option>
                                    @foreach (array_unique(array_column($reports, 'riskRate')) as $rate)
                                        @if ($rate)
                                            <option value="{{ $rate }}">{{ $rate }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="departmentFilter" class="form-label">Department</label>
                                <select id="departmentFilter" class="form-select">
                                    <option value="">All Departments</option>
                                    @foreach (array_unique(array_column($reports, 'department')) as $department)
                                        @if ($department)
                                            <option value="{{ $department }}">{{ $department }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="processTypeFilter" class="form-label">Process Type</label>
                                <select id="processTypeFilter" class="form-select">
                                    <option value="">All Process Types</option>
                                    @foreach (array_unique(array_column($reports, 'processType')) as $processType)
                                        @if ($processType)
                                            <option value="{{ $processType }}">{{ $processType }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="batchFilter" class="form-label">Exception Batch</label>
                                <select id="batchFilter" class="form-select">
                                    <option value="">All Batches</option>
                                    @foreach (array_unique(array_column($reports, 'exceptionBatch')) as $batch)
                                        @if ($batch)
                                            <option value="{{ $batch }}">{{ $batch }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-3">
                                <label for="dateFromFilter" class="form-label">From Date</label>
                                <input type="text" id="dateFromFilter" class="form-control datepicker"
                                    placeholder="Select start date">
                            </div>
                            <div class="col-md-3">
                                <label for="dateToFilter" class="form-label">To Date</label>
                                <input type="text" id="dateToFilter" class="form-control datepicker"
                                    placeholder="Select end date">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="button" id="resetFilters" class="btn btn-secondary me-2">
                                    <i class="fas fa-undo me-1"></i>Reset Filters
                                </button>
                                <button type="button" id="applyFilters" class="btn btn-primary">
                                    <i class="fas fa-search me-1"></i>Apply Filters
                                </button>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="button" id="downloadReport" class="btn btn-success" disabled>
                                    <i class="fas fa-download me-1"></i>Download Word Report
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Preview Card -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-light">
                    <h4 class="card-title mb-0">
                        <i class="fas fa-file-alt me-2"></i>Report Preview
                        <span id="recordCount" class="badge bg-primary ms-2">0 records</span>
                    </h4>
                </div>
                <div class="card-body" style="min-height: 400px;">
                    <!-- Loading State -->
                    <div id="loadingState" class="text-center py-5" style="display: none;">
                        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <h5 class="mt-3 text-muted">Preparing report data...</h5>
                        <p class="text-muted">Please wait while we process your filtered data.</p>
                    </div>

                    <!-- Empty State -->
                    <div id="emptyState" class="text-center py-5">
                        <i class="fas fa-filter fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">No filters applied</h5>
                        <p class="text-muted">Apply filters above to preview the report data that will be included in your Word document.</p>
                    </div>

                    <!-- Preview Content -->
                    <div id="previewContent" style="display: none;">
                        <div class="mb-4">
                            <h5 class="text-primary border-bottom pb-2">
                                <i class="fas fa-info-circle me-2"></i>Applied Filters Summary
                            </h5>
                            <div id="filterSummary" class="alert alert-info mb-4"></div>
                        </div>

                        <div id="branchPreview"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

        <script>
            $(document).ready(function() {
                let filteredData = [];
                let allReports = @json($reports);
                let batches = @json($batches);
                let groups = @json($groups);

                // Initialize date picker
                flatpickr('.datepicker', {
                    dateFormat: 'Y-m-d',
                    allowInput: true
                });

                // Helper function to get branch name
                function getBranchName(exceptionBatchId) {
                    for (let batch of batches) {
                        if (batch.id == exceptionBatchId) {
                            for (let group of groups) {
                                if (group.id == batch.activityGroupId) {
                                    return group.branchName;
                                }
                            }
                        }
                    }
                    return 'N/A';
                }

                // Filter function
                function applyFilters() {
                    showLoading();

                    // Simulate processing delay
                    setTimeout(() => {
                        const filters = {
                            auditor: $('#auditorFilter').val(),
                            status: $('#statusFilter').val(),
                            riskRate: $('#riskRateFilter').val(),
                            department: $('#departmentFilter').val(),
                            processType: $('#processTypeFilter').val(),
                            batch: $('#batchFilter').val(),
                            dateFrom: $('#dateFromFilter').val(),
                            dateTo: $('#dateToFilter').val()
                        };

                        filteredData = allReports.filter(report => {
                            // Apply each filter
                            if (filters.auditor && report.auditorName !== filters.auditor) return false;
                            if (filters.status && report.status !== filters.status) return false;
                            if (filters.riskRate && report.riskRate !== filters.riskRate) return false;
                            if (filters.department && report.department !== filters.department) return false;
                            if (filters.processType && report.processType !== filters.processType) return false;
                            if (filters.batch && report.exceptionBatch !== filters.batch) return false;

                            // Date filtering
                            if (filters.dateFrom || filters.dateTo) {
                                const occurrenceDate = report.occurrenceDate ? report.occurrenceDate.split('T')[0] : null;
                                if (filters.dateFrom && occurrenceDate && occurrenceDate < filters.dateFrom) return false;
                                if (filters.dateTo && occurrenceDate && occurrenceDate > filters.dateTo) return false;
                            }

                            return true;
                        });

                        displayPreview(filteredData, filters);
                        hideLoading();
                    }, 1000);
                }

                function showLoading() {
                    $('#loadingState').show();
                    $('#emptyState').hide();
                    $('#previewContent').hide();
                    $('#downloadReport').prop('disabled', true);
                }

                function hideLoading() {
                    $('#loadingState').hide();
                }

                function displayPreview(data, filters) {
                    if (data.length === 0 && Object.values(filters).some(f => f !== '')) {
                        $('#emptyState').html(`
                            <i class="fas fa-search fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted">No matching records found</h5>
                            <p class="text-muted">Try adjusting your filters to see results.</p>
                        `).show();
                        $('#previewContent').hide();
                        $('#downloadReport').prop('disabled', true);
                        return;
                    }

                    if (data.length === 0) {
                        $('#emptyState').show();
                        $('#previewContent').hide();
                        $('#downloadReport').prop('disabled', true);
                        return;
                    }

                    // Update record count
                    $('#recordCount').text(`${data.length} records`);

                    // Display filter summary
                    const activeFilters = [];
                    Object.keys(filters).forEach(key => {
                        if (filters[key]) {
                            const label = key.charAt(0).toUpperCase() + key.slice(1).replace(/([A-Z])/g, ' $1');
                            activeFilters.push(`<strong>${label}:</strong> ${filters[key]}`);
                        }
                    });

                    $('#filterSummary').html(
                        activeFilters.length > 0
                            ? activeFilters.join(' | ')
                            : 'No specific filters applied - showing all records'
                    );

                    // Group data by branch
                    const branchGroups = {};
                    data.forEach(report => {
                        const branchName = getBranchName(report.exceptionBatchId);
                        if (!branchGroups[branchName]) {
                            branchGroups[branchName] = [];
                        }
                        branchGroups[branchName].push(report);
                    });

                    // Display branch preview
                    let previewHtml = '';
                    Object.keys(branchGroups).forEach(branchName => {
                        const branchReports = branchGroups[branchName];
                        previewHtml += `
                            <div class="mb-4">
                                <h5 class="text-success border-bottom pb-2">
                                    <i class="fas fa-map-marker-alt me-2"></i>Branch: ${branchName}
                                    <span class="badge bg-success ms-2">${branchReports.length} exceptions</span>
                                </h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card border-left-primary">
                                            <div class="card-body py-3">
                                                <h6 class="text-muted mb-2">Risk Distribution</h6>
                                                <div class="risk-stats">
                        `;

                        // Risk statistics
                        const riskStats = {};
                        branchReports.forEach(report => {
                            riskStats[report.riskRate] = (riskStats[report.riskRate] || 0) + 1;
                        });

                        Object.keys(riskStats).forEach(risk => {
                            const badgeClass = risk === 'High' ? 'danger' : risk === 'Medium' ? 'warning' : 'success';
                            previewHtml += `<span class="badge bg-${badgeClass} me-2">${risk}: ${riskStats[risk]}</span>`;
                        });

                        previewHtml += `
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card border-left-info">
                                            <div class="card-body py-3">
                                                <h6 class="text-muted mb-2">Status Overview</h6>
                                                <div class="status-stats">
                        `;

                        // Status statistics
                        const statusStats = {};
                        branchReports.forEach(report => {
                            statusStats[report.status] = (statusStats[report.status] || 0) + 1;
                        });

                        Object.keys(statusStats).forEach(status => {
                            const badgeClass = status === 'RESOLVED' ? 'success' : status === 'PENDING' ? 'warning' : 'secondary';
                            previewHtml += `<span class="badge bg-${badgeClass} me-2">${status}: ${statusStats[status]}</span>`;
                        });

                        previewHtml += `
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <h6 class="text-muted">Sample Exceptions Preview:</h6>
                                    <div class="list-group list-group-flush">
                        `;

                        // Show first 3 exceptions as preview
                        branchReports.slice(0, 3).forEach(report => {
                            previewHtml += `
                                <div class="list-group-item border-start border-3 border-${report.riskRate === 'High' ? 'danger' : report.riskRate === 'Medium' ? 'warning' : 'success'}">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h6 class="mb-1">${report.exceptionTitle || 'Exception'}</h6>
                                        <small class="text-muted">${report.processType}</small>
                                    </div>
                                    <p class="mb-1 text-truncate" style="max-width: 500px;">${report.exception}</p>
                                    <small class="text-muted">Risk: ${report.riskRate} | Status: ${report.status}</small>
                                </div>
                            `;
                        });

                        if (branchReports.length > 3) {
                            previewHtml += `
                                <div class="list-group-item text-center text-muted">
                                    <small>... and ${branchReports.length - 3} more exceptions</small>
                                </div>
                            `;
                        }

                        previewHtml += `
                                    </div>
                                </div>
                            </div>
                        `;
                    });

                    $('#branchPreview').html(previewHtml);
                    $('#emptyState').hide();
                    $('#previewContent').show();
                    $('#downloadReport').prop('disabled', false);
                }

                // Event handlers
                $('#applyFilters').on('click', applyFilters);

                $('#resetFilters').on('click', function() {
                    $('#reportFilters')[0].reset();
                    filteredData = [];
                    $('#emptyState').html(`
                        <i class="fas fa-filter fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">No filters applied</h5>
                        <p class="text-muted">Apply filters above to preview the report data that will be included in your Word document.</p>
                    `).show();
                    $('#previewContent').hide();
                    $('#recordCount').text('0 records');
                    $('#downloadReport').prop('disabled', true);
                });

                // Date validation
                $('#dateFromFilter, #dateToFilter').on('change', function() {
                    const dateFrom = $('#dateFromFilter').val();
                    const dateTo = $('#dateToFilter').val();

                    if (dateFrom && dateTo && dateFrom > dateTo) {
                        alert('End date must be after start date');
                        $(this).val('');
                    }
                });

                // Download report
                $('#downloadReport').on('click', function() {
                    if (filteredData.length === 0) {
                        alert('No data to export. Please apply filters first.');
                        return;
                    }

                    showLoading();

                    // Get current filter values
                    const filters = {
                        auditor: $('#auditorFilter').val(),
                        status: $('#statusFilter').val(),
                        riskRate: $('#riskRateFilter').val(),
                        department: $('#departmentFilter').val(),
                        processType: $('#processTypeFilter').val(),
                        batch: $('#batchFilter').val(),
                        dateFrom: $('#dateFromFilter').val(),
                        dateTo: $('#dateToFilter').val()
                    };

                    // Create form to submit data
                    const form = $('<form>', {
                        'method': 'POST',
                        'action': '{{ route("reports.export-word") }}',
                        'target': '_blank'
                    });

                    // Add CSRF token
                    form.append($('<input>', {
                        'type': 'hidden',
                        'name': '_token',
                        'value': '{{ csrf_token() }}'
                    }));

                    // Add filters
                    form.append($('<input>', {
                        'type': 'hidden',
                        'name': 'filters',
                        'value': JSON.stringify(filters)
                    }));

                    // Add data
                    form.append($('<input>', {
                        'type': 'hidden',
                        'name': 'data',
                        'value': JSON.stringify(filteredData)
                    }));

                    // Submit form
                    form.appendTo('body').submit().remove();

                    setTimeout(() => {
                        hideLoading();
                    }, 2000);
                });

                // Auto-apply filters on change (optional)
                $('#auditorFilter, #statusFilter, #riskRateFilter, #departmentFilter, #processTypeFilter, #batchFilter').on('change', function() {
                    if ($(this).val() !== '') {
                        applyFilters();
                    }
                });
            });
        </script>

        <style>
            .border-left-primary { border-left: 4px solid #007bff !important; }
            .border-left-info { border-left: 4px solid #17a2b8 !important; }
            .card-body { padding: 1rem; }
            .risk-stats .badge, .status-stats .badge { font-size: 0.8em; }
        </style>
    @endpush
</x-base-layout>
