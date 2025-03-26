<x-base-layout>
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
        <div class="row">
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
                                        {{--  @foreach ($batches as $batch)  --}}
                                        <option value="">Batch Name</option>
                                        {{--  @endforeach  --}}
                                    </select>
                                </div>
                                <!-- Department Filter -->
                                <div class="col-md-3">
                                    <label for="departmentFilter" class="form-label">Department</label>
                                    <select id="departmentFilter" class="select2 form-control" name="departmentId"
                                        data-placeholder="Choose ...">
                                        <option value="">Select ....</option>
                                        {{--  @foreach ($departments as $department)  --}}
                                        <option value="">
                                            department name</option>
                                        {{--  @endforeach  --}}
                                    </select>
                                </div>
                                <!-- KPI Filter -->
                                <div class="col-md-3">
                                    <label for="kpiFilter" class="form-label">Group</label>
                                    <select id="kpiFilter" class="select2 form-control" name="kpiId"
                                        data-placeholder="Choose ...">
                                        <option value="">Select ....</option>
                                        {{--  @foreach ($kpis as $kpi)  --}}
                                        <option value="">kpi name</option>
                                        {{--  @endforeach  --}}
                                    </select>
                                </div>
                                <!-- Employee Filter -->
                                <div class="col-md-3">
                                    <label for="employeeFilter" class="form-label">Auditor</label>
                                    <select id="employeeFilter" class="select2 form-control" name="employeeId"
                                        data-placeholder="Choose ...">
                                        <option value="">Select ....</option>
                                        {{--  @foreach ($employees as $employee)  --}}
                                        <option value="">
                                            employee name</option>
                                        {{--  @endforeach  --}}
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
        </div>

    </div>

    <div class="mt-4 mb-4" style="background-color: gray; height: 1px;"></div>



    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">Exceptions Reports Table</h4>

                    <div class="table-responsive">
                        <table id="datatable-buttons"
                            class="reportsTable table table-bordered table-striped table-hover dt-responsive nowrap">
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
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="reportsTableBody">
                                {{--  {{ dd($reports->data) }}  --}}
                                @forelse ($reports as $report)
                                    <tr>

                                        <td>{{ $report->exception ?? 'N/A' }}</td>
                                        <td>{{ $report->rootCause ?? 'N/A' }}</td>
                                        <td>{{ $report->auditorName ?? 'N/A' }},
                                            {{ $report->auditeeName ?? 'No Auditee' }}</td>
                                        <td>{{ $report->processType ?? 'N/A' }}</td>
                                        <td>{{ $report->riskRate ?? 'N/A' }}</td>
                                        <td>Branch Here!</td>
                                        <td>{{ $report->department ?? 'N/A' }}</td>
                                        <td>{{ $report->status ?? 'N/A' }}</td>
                                        <td>{{ $report->occurrenceDate ?? 'N/A' }}</td>
                                        <td>{{ $report->resolutionDate ?? 'N/A' }}</td>
                                        <td><a href=""><span
                                                    class="badge rounded-pill bg-primary">View</span></a>
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

                    {{--  <script>
                        document.getElementById('filterButton').addEventListener('click', function() {
                            // Gather filter criteria
                            const batchId = $('#batchFilter').val();
                            const departmentId = $('#departmentFilter').val();
                            const kpiId = $('#kpiFilter').val();
                            const employeeId = $('#employeeFilter').val();

                            // Make AJAX call to fetch filtered data
                            $.ajax({
                                url: "{{ route('report.index') }}", // Replace with your route
                                type: "GET",
                                data: {
                                    batchId: batchId,
                                    departmentId: departmentId,
                                    kpiId: kpiId,
                                    employeeId: employeeId
                                },
                                success: function(response) {
                                    // Update the table body with new data
                                    const tableBody = $('#datatable-buttons tbody');
                                    tableBody.empty();

                                    if (response.reports.length === 0) {
                                        tableBody.append(
                                            '<tr><td colspan="10" class="text-center">No data available</td></tr>');
                                    } else {
                                        response.reports.forEach(report => {
                                            report.employees.forEach(employee => {
                                                const row = `
                                <tr>
                                    <td>${report.batchName ?? 'N/A'}</td>
                                    <td>${employee.employeeName ?? 'N/A'}</td>
                                    <td>${employee.totalScore?.grade ?? 'N/A'}</td>
                                    <td>${employee.totalScore?.totalKpiScore ?? 'N/A'}</td>
                                    <td>${employee.totalScore?.remark ?? 'N/A'}</td>
                                    <td>${employee.departmentName ?? 'N/A'}</td>
                                    <td>${employee.roleName ?? 'N/A'}</td>
                                    <td>${employee.supervisorName ?? 'N/A'}</td>
                                    <td>${employee.probeName ?? 'N/A'}</td>
                                    <td><a href="#"><span class="badge rounded-pill bg-primary">View</span></a></td>
                                </tr>
                            `;
                                                tableBody.append(row);
                                            });
                                        });
                                    }
                                },
                                error: function() {
                                    alert('Failed to fetch filtered data. Please try again.');
                                }
                            });
                        });
                    </script>  --}}


                </div>
            </div>
        </div> <!-- end col -->
    </div>
    </div>
</x-base-layout>
