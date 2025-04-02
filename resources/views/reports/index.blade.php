<x-base-layout>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">



    <div class="container-fluid px-1">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Overview of Exceptions Reports</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->
        {{--  {{ dd($reports) }}  --}}
        {{--  <div class="row">
            <div class="col-12">
                <form id="filterForm" method="POST" action="">
                    @csrf
                    <div class="card">
                        <div class="card-body">
                            <div class="row align-items-center g-2">
                                <!-- Batch Filter -->
                                <div class="col-md-3">
                                    <label for="batchFilter" class="form-label">Exception</label>
                                    <select id="batchFilter" class="select2 form-control" name="batchId"
                                        data-placeholder="Choose ...">
                                        <option value="">Select ....</option>

                                        <option value="">Batch Name</option>

                                    </select>
                                </div>
                                <!-- Department Filter -->
                                <div class="col-md-3">
                                    <label for="departmentFilter" class="form-label">Department</label>
                                    <select id="departmentFilter" class="select2 form-control" name="departmentId"
                                        data-placeholder="Choose ...">
                                        <option value="">Select ....</option>

                                        <option value="">
                                            department name</option>

                                    </select>
                                </div>
                                <!-- KPI Filter -->
                                <div class="col-md-3">
                                    <label for="kpiFilter" class="form-label">Group</label>
                                    <select id="kpiFilter" class="select2 form-control" name="kpiId"
                                        data-placeholder="Choose ...">
                                        <option value="">Select ....</option>

                                        <option value="">kpi name</option>

                                    </select>
                                </div>
                                <!-- Employee Filter -->
                                <div class="col-md-3">
                                    <label for="employeeFilter" class="form-label">Auditor</label>
                                    <select id="employeeFilter" class="select2 form-control" name="employeeId"
                                        data-placeholder="Choose ...">
                                        <option value="">Select ....</option>

                                        <option value="">
                                            employee name</option>

                                    </select>
                                </div>
                                <!-- Buttons -->
                                <div class="col-12 mt-3 d-flex justify-content-end gap-2">
                                    <button id="filterButton" type="submit" class="btn btn-success">
                                        <i class="bx bx-filter-alt"></i> Filter
                                    </button>
                                    <a href="" class="btn btn-primary">
                                        <i class="bx bx-rotate-left"></i> Refresh
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>  --}}

    </div>

    <div class="mt-4 mb-4" style="background-color: gray; height: 1px;"></div>



    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">Exceptions Reports Table</h4>
                    <div class="table-responsive">
                        <table id="reportsTable" class="table table-bordered table-striped table-hover">
                            <thead>
                                <tr>
                                    <th class="col-3">Exception</th>
                                    <th class="col-3">Root Cause</th>
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
                                    <tr>
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
                                                // Get branch name from exception batch data
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
        </div> <!-- end col -->
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


        <script>
            $(document).ready(function() {
                $('#reportsTable').DataTable({
                    dom: 'Bfrtip',
                    buttons: [{
                            extend: 'excelHtml5',
                            text: 'Export to Excel',
                            title: 'Exceptions Report',
                            exportOptions: {
                                // Export only visible columns
                                columns: ':visible',
                                // Export only filtered data if search is applied
                                modifier: {
                                    search: 'applied',
                                    order: 'applied'
                                }
                            }
                        },
                        {
                            extend: 'colvis',
                            text: 'Column Visibility',
                            columns: ':not(.noVis)' // Exclude columns you don't want to be hideable
                        }
                    ],
                    responsive: true,
                    pageLength: 25,
                    lengthMenu: [
                        [10, 25, 50, 100, -1],
                        [10, 25, 50, 100, "All"]
                    ],
                    columnDefs: [{
                            targets: [0, 1], // Exception and Root Cause columns
                            render: function(data, type, row) {
                                if (type === 'display' && data.length > 50) {
                                    return data.substr(0, 50) + '...';
                                }
                                return data;
                            }
                        },
                        {
                            targets: 5, // Branch column
                            render: function(data, type, row) {
                                // Format branch name if needed
                                return data;
                            }
                        },
                        {
                            targets: [8, 9], // Date columns
                            render: function(data, type, row) {
                                if (type === 'display' || type === 'filter') {
                                    if (data === 'N/A') return data;
                                    return data; // Already formatted in HTML
                                }
                                return data;
                            }
                        }
                    ],
                    initComplete: function() {
                        // Add custom search inputs for specific columns if needed
                        this.api().columns().every(function() {
                            var column = this;
                            if (column.index() === 4) { // Risk Rate column
                                var select = $(
                                        '<select><option value="">All Risk Rates</option></select>')
                                    .appendTo($(column.header()))
                                    .on('change', function() {
                                        var val = $.fn.dataTable.util.escapeRegex(
                                            $(this).val()
                                        );
                                        column.search(val ? '^' + val + '$' : '', true, false)
                                            .draw();
                                    });
                                column.data().unique().sort().each(function(d, j) {
                                    select.append('<option value="' + d + '">' + d +
                                        '</option>');
                                });
                            }
                        });
                    }
                });
            });
        </script>
    @endpush
</x-base-layout>
