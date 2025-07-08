<x-base-layout>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <div class="container-fluid px-1">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Overview of Exceptions Reports</h4>
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
                    <h4 class="card-title mb-4">Export Filters</h4>
                    <form id="exportFilters">
                        <div class="row">
                            <div class="col-md-2">
                                <label for="batchFilter" class="form-label">Batch</label>
                                <select id="batchFilter" class="form-select">
                                    <option value="">All Batches</option>
                                    @foreach ($batches as $batch)
                                        <option value="{{ $batch->id }}">{{ $batch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="branchFilter" class="form-label">Branch</label>
                                <select id="branchFilter" class="form-select">
                                    <option value="">All Branches</option>
                                    @foreach (array_unique(array_column($groups, 'branchName')) as $branchName)
                                        <option value="{{ $branchName }}">{{ $branchName }}</option>
                                    @endforeach
                                </select>
                            </div>
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
                                <button type="button" id="resetFilters" class="btn btn-secondary">Reset Filters</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">Exceptions Reports Table</h4>
                    <div class="table-responsive">
                        <table id="reportsTable" class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Exception Title</th>
                                    <th>Exception</th>
                                    <th>Root Cause</th>
                                    <th>Participants</th>
                                    <th>Process Type</th>
                                    <th>Sub Process Type</th>
                                    <th>Risk Rate</th>
                                    <th>Branch</th>
                                    <th>Response</th>
                                    <th>Department</th>
                                    <th>Status</th>
                                    <th>Occurrence Date</th>
                                    <th>Resolution Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($reports as $report)
                                    <tr data-batch="{{ $report->exceptionBatchId }}"
                                        data-branch="@php
                                            $branchName = 'N/A';
                                            foreach ($batches as $batch) {
                                                if ($batch->id == $report->exceptionBatchId) {
                                                    foreach ($groups as $group) {
                                                        if ($group->id == $batch->activityGroupId) {
                                                            $branchName = $group->branchName;
                                                            break;
                                                        }
                                                    }
                                                    break;
                                                }
                                            }
                                            echo $branchName;
                                        @endphp"
                                        data-auditor="{{ $report->auditorName }}"
                                        data-status="{{ $report->status }}"
                                        data-risk-rate="{{ $report->riskRate }}"
                                        data-occurrence-date="{{ $report->occurrenceDate ? \Carbon\Carbon::parse($report->occurrenceDate)->format('Y-m-d') : '' }}">
                                        <td>{{ $report->exceptionTitle ?? 'N/A' }}</td>
                                        <td>{{ $report->exception ?? 'N/A' }}</td>
                                        <td>{{ $report->rootCause ?? 'N/A' }}</td>
                                        <td>
                                            {{ $report->auditorName ?? 'N/A' }},
                                            {{ $report->auditeeName ?? 'No Auditee' }}
                                        </td>
                                        <td>{{ $report->processType ?? 'N/A' }}</td>
                                        <td>{{ $report->subProcessType ?? 'N/A' }}</td>
                                        <td>{{ $report->riskRate ?? 'N/A' }}</td>
                                        <td>{{ $branchName }}</td>
                                        <td>{{ $report->statusComment ?? 'N/A' }}</td>
                                        <td>{{ $report->department ?? 'N/A' }}</td>
                                        <td>{{ $report->status ?? 'N/A' }}</td>
                                        <td>{{ $report->occurrenceDate ? \Carbon\Carbon::parse($report->occurrenceDate)->format('Y-m-d') : 'N/A' }}</td>
                                        <td>{{ $report->resolutionDate ? \Carbon\Carbon::parse($report->resolutionDate)->format('Y-m-d') : 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="13" class="text-center">No data available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.datatables.net/2.2.2/js/dataTables.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

        <script>
            $(document).ready(function() {
                // Initialize date picker
                flatpickr('.datepicker', {
                    dateFormat: 'Y-m-d',
                    allowInput: true
                });

                // Initialize DataTable
                var table = $('#reportsTable').DataTable({
                    dom: 'Bfrtip',
                    buttons: [
                        {
                            extend: 'excelHtml5',
                            text: 'Export to Excel',
                            title: 'Exceptions Report',
                            exportOptions: {
                                columns: ':visible',
                                modifier: {
                                    search: 'applied',
                                    order: 'applied',
                                    page: 'all'
                                }
                            },
                            customize: function(xlsx) {
                                var sheet = xlsx.xl.worksheets['sheet1.xml'];

                                // Add filter information at the top
                                var filterInfo = getFilterInfo();
                                if (filterInfo) {
                                    // Insert filter information before the table
                                    $('row:first', sheet).before(
                                        '<row r="1"><c r="A1" t="inlineStr"><is><t>Applied Filters: ' + filterInfo + '</t></is></c></row>'
                                    );
                                }

                                // Style headers
                                $('row:first c', sheet).attr('s', '2');
                            },
                            filename: function() {
                                // Dynamic filename based on filters
                                let filename = 'Exceptions_Report';
                                const batch = $('#batchFilter').val();
                                const branch = $('#branchFilter').val();
                                const auditor = $('#auditorFilter').val();
                                const status = $('#statusFilter').val();
                                const riskRate = $('#riskRateFilter').val();
                                const dateFrom = $('#dateFromFilter').val();
                                const dateTo = $('#dateToFilter').val();

                                if (batch) filename += `_Batch-${batch}`;
                                if (branch) filename += `_Branch-${branch}`;
                                if (auditor) filename += `_Auditor-${auditor}`;
                                if (status) filename += `_Status-${status}`;
                                if (riskRate) filename += `_Risk-${riskRate}`;
                                if (dateFrom) filename += `_From-${dateFrom}`;
                                if (dateTo) filename += `_To-${dateTo}`;

                                return filename;

                            }
                        },
                        {
                            text: 'Export to PDF',
                            action: function (e, dt, button, config) {
                                // Get current filter values
                                const filters = {
                                    batch: $('#batchFilter').val(),
                                    branch: $('#branchFilter').val(),
                                    auditor: $('#auditorFilter').val(),
                                    status: $('#statusFilter').val(),
                                    riskRate: $('#riskRateFilter').val(),
                                    dateFrom: $('#dateFromFilter').val(),
                                    dateTo: $('#dateToFilter').val()
                                };

                                // Get filtered data
                                const filteredData = [];
                                dt.rows({ search: 'applied' }).every(function() {
                                    const rowData = this.data();
                                    filteredData.push(rowData);
                                });

                                // Create form to submit data
                                const form = $('<form>', {
                                    'method': 'POST',
                                    'action': '/reports/export-pdf',
                                    'target': '_blank'
                                });

                                // Add CSRF token
                                form.append($('<input>', {
                                    'type': 'hidden',
                                    'name': '_token',
                                    'value': $('meta[name="csrf-token"]').attr('content')
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
                            }
                        },
                        {
                            extend: 'colvis',
                            text: 'Column Visibility'
                        }
                    ],
                    responsive: true,
                    pageLength: 25,
                    lengthMenu: [
                        [10, 25, 50, 100, -1],
                        [10, 25, 50, 100, "All"]
                    ],
                    columnDefs: [
                        {
                            // Exception Title and Exception columns - truncate long text
                            targets: [0, 1, 2],
                            render: function(data, type, row) {
                                if (type === 'display' && data && data.length > 50) {
                                    return '<span title="' + data + '">' + data.substr(0, 50) + '...</span>';
                                }
                                return data || 'N/A';
                            }
                        },
                        {
                            // Response column - truncate long text
                            targets: [8],
                            render: function(data, type, row) {
                                if (type === 'display' && data && data.length > 30) {
                                    return '<span title="' + data + '">' + data.substr(0, 30) + '...</span>';
                                }
                                return data || 'N/A';
                            }
                        },
                        {
                            // Date columns - ensure proper formatting
                            targets: [11, 12],
                            render: function(data, type, row) {
                                if (type === 'display' || type === 'type') {
                                    return data === 'N/A' ? 'N/A' : data;
                                }
                                return data;
                            }
                        }
                    ],
                    order: [[11, 'desc']], // Sort by occurrence date descending
                    searching: true,
                    ordering: true,
                    info: true,
                    autoWidth: false
                });

                // Custom filtering function
                function applyCustomFilters() {
                    // Clear existing custom filters
                    $.fn.dataTable.ext.search.pop();

                    // Add new custom filter
                    $.fn.dataTable.ext.search.push(
                        function(settings, data, dataIndex) {
                            if (settings.nTable.id !== 'reportsTable') {
                                return true;
                            }

                            const row = table.row(dataIndex).node();
                            const $row = $(row);

                            // Get filter values
                            const batch = $('#batchFilter').val();
                            const branch = $('#branchFilter').val();
                            const auditor = $('#auditorFilter').val();
                            const status = $('#statusFilter').val();
                            const riskRate = $('#riskRateFilter').val();
                            const dateFrom = $('#dateFromFilter').val();
                            const dateTo = $('#dateToFilter').val();

                            // Get row data
                            const rowBatch = $row.data('batch');
                            const rowBranch = $row.data('branch');
                            const rowAuditor = $row.data('auditor');
                            const rowStatus = $row.data('status');
                            const rowRiskRate = $row.data('risk-rate');
                            const rowDate = $row.data('occurrence-date');

                            // Apply filters
                            if (batch && String(rowBatch) !== String(batch)) return false;
                            if (branch && String(rowBranch) !== String(branch)) return false;
                            if (auditor && String(rowAuditor) !== String(auditor)) return false;
                            if (status && String(rowStatus) !== String(status)) return false;
                            if (riskRate && String(rowRiskRate) !== String(riskRate)) return false;

                            // Date filtering
                            if (dateFrom && rowDate && rowDate < dateFrom) return false;
                            if (dateTo && rowDate && rowDate > dateTo) return false;

                            return true;
                        }
                    );

                    table.draw();
                }

                // Get filter information for export
                function getFilterInfo() {
                    const filters = [];
                    const batch = $('#batchFilter').val();
                    const branch = $('#branchFilter').val();
                    const auditor = $('#auditorFilter').val();
                    const status = $('#statusFilter').val();
                    const riskRate = $('#riskRateFilter').val();
                    const dateFrom = $('#dateFromFilter').val();
                    const dateTo = $('#dateToFilter').val();

                    if (batch) filters.push(`Batch: ${$('#batchFilter option:selected').text()}`);
                    if (branch) filters.push(`Branch: ${branch}`);
                    if (auditor) filters.push(`Auditor: ${auditor}`);
                    if (status) filters.push(`Status: ${status}`);
                    if (riskRate) filters.push(`Risk Rate: ${riskRate}`);
                    if (dateFrom) filters.push(`From: ${dateFrom}`);
                    if (dateTo) filters.push(`To: ${dateTo}`);

                    return filters.length > 0 ? filters.join(', ') : null;
                }

                // Apply filters on change
                $('#batchFilter, #branchFilter, #auditorFilter, #statusFilter, #riskRateFilter').on('change', function() {
                    applyCustomFilters();
                });

                // Date filter validation and application
                $('#dateFromFilter, #dateToFilter').on('change', function() {
                    const dateFrom = $('#dateFromFilter').val();
                    const dateTo = $('#dateToFilter').val();

                    if (dateFrom && dateTo && dateFrom > dateTo) {
                        alert('End date must be after start date');
                        $(this).val('');
                        return;
                    }

                    applyCustomFilters();
                });

                // Reset all filters
                $('#resetFilters').on('click', function() {
                    $('#exportFilters')[0].reset();

                    // Clear custom filters
                    $.fn.dataTable.ext.search.pop();

                    // Reset table
                    table.search('').columns().search('').draw();
                });

                // Initialize with no custom filters
                applyCustomFilters();
            });
        </script>
    @endpush
</x-base-layout>
