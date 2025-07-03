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
                            <!-- Existing filters -->
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
                            <!-- New Risk Rate Filter -->
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
                                <button type="button" id="resetFilters" class="btn btn-secondary">Reset
                                    Filters</button>
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
                                    <th>Exception</th>
                                    <th>Root Cause</th>
                                    <th>Participants</th>
                                    <th>Process Type</th>
                                    <th>Risk Rate</th>
                                    <th>Branch</th>
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
foreach ($batches as $batch) {
                                                if ($batch->id == $report->exceptionBatchId) {
                                                    foreach ($groups as $group) {
                                                        if ($group->id == $batch->activityGroupId) {
                                                            echo $group->branchName;
                                                            break;
                                                        }
                                                    }
                                                    break;
                                                }
                                            } @endphp"
                                        data-auditor="{{ $report->auditorName }}" data-status="{{ $report->status }}"
                                        data-occurrence-date="{{ $report->occurrenceDate ? \Carbon\Carbon::parse($report->occurrenceDate)->format('Y-m-d') : '' }}">
                                        <td>{{ $report->exception ?? 'N/A' }}</td>
                                        <td>{{ $report->rootCause ?? 'N/A' }}</td>
                                        <td>
                                            {{ $report->auditorName ?? 'N/A' }},
                                            {{ $report->auditeeName ?? 'No Auditee' }}
                                        </td>
                                        <td>{{ $report->processType ?? 'N/A' }}</td>
                                        <td>{{ $report->riskRate ?? 'N/A' }}</td>
                                        <td>
                                            @php
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
                                            @endphp
                                        </td>
                                        <td>{{ $report->department ?? 'N/A' }}</td>
                                        <td>{{ $report->status ?? 'N/A' }}</td>
                                        <td>{{ $report->occurrenceDate ? \Carbon\Carbon::parse($report->occurrenceDate)->format('Y-m-d') : 'N/A' }}
                                        </td>
                                        <td>{{ $report->resolutionDate ? \Carbon\Carbon::parse($report->resolutionDate)->format('Y-m-d') : 'N/A' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center">No data available</td>
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
                    buttons: [{
                            extend: 'excelHtml5',
                            text: 'Export to Excel',
                            title: 'Exceptions Report',
                            exportOptions: {
                                columns: ':visible',
                                modifier: {
                                    // This ensures we export only what's visible in the table
                                    search: 'applied',
                                    order: 'applied',
                                    page: 'all',
                                    // Custom filter to match our applied filters
                                    filter: 'applied'
                                },
                                // Customize the data being exported
                                format: {
                                    body: function(data, row, column, node) {
                                        return data;
                                    }
                                }
                            },
                            customize: function(xlsx) {
                                // Add filter information to the exported file
                                var sheet = xlsx.xl.worksheets['sheet1.xml'];
                                $('row:first c', sheet).attr('s', '2'); // Header styling
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
                    columnDefs: [{
                            targets: [0, 1],
                            render: function(data, type, row) {
                                if (type === 'display' && data.length > 50) {
                                    return data.substr(0, 50) + '...';
                                }
                                return data;
                            }
                        },
                        {
                            targets: [8, 9],
                            render: function(data, type, row) {
                                if (type === 'display' || type === 'filter') {
                                    if (data === 'N/A') return data;
                                    return data;
                                }
                                return data;
                            }
                        }
                    ]
                });

                // Filter functions
                // Custom filtering function that works with DataTables native filtering
                function applyCustomFilters() {
                    $.fn.dataTable.ext.search.push(
                        function(settings, data, dataIndex) {
                            const row = table.row(dataIndex).node();
                            const rowBatch = $(row).data('batch');
                            const rowBranch = $(row).data('branch');
                            const rowAuditor = $(row).data('auditor');
                            const rowStatus = $(row).data('status');
                            const rowRiskRate = $(row).find('td:eq(4)').text().trim();
                            const rowDate = $(row).data('occurrence-date');

                            const batch = $('#batchFilter').val();
                            const branch = $('#branchFilter').val();
                            const auditor = $('#auditorFilter').val();
                            const status = $('#statusFilter').val();
                            const riskRate = $('#riskRateFilter').val();
                            const dateFrom = $('#dateFromFilter').val();
                            const dateTo = $('#dateToFilter').val();

                            if (batch && rowBatch != batch) return false;
                            if (branch && rowBranch != branch) return false;
                            if (auditor && rowAuditor != auditor) return false;
                            if (status && rowStatus != status) return false;
                            if (riskRate && rowRiskRate != riskRate) return false;
                            if (dateFrom && rowDate && rowDate < dateFrom) return false;
                            if (dateTo && rowDate && rowDate > dateTo) return false;

                            return true;
                        }
                    );

                    table.draw();
                    $.fn.dataTable.ext.search.pop(); // Remove the filter after applying
                }

                // Apply filters on change
                $('#batchFilter, #branchFilter, #auditorFilter, #statusFilter, #riskRateFilter').on('change',
            function() {
                    applyCustomFilters();
                });

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
                    $('#exportFilters').trigger('reset');
                    table.search('').columns().search('').draw();
                });
            });
        </script>
    @endpush
</x-base-layout>
