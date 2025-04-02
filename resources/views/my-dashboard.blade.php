<x-base-layout>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <div class="container-fluid">

        @if (isset($error))
            <div class="alert alert-danger">{{ $error }}</div>
        @endif

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">My Group Exception Dashboard</h4>
                    <div class="text-end">
                        <span class="badge bg-primary">
                            Member of {{ $employeeGroups->count() }} group(s)
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <!-- Summary Cards -->
        <div class="row">
            <div class="col-md-4">
                <div class="card mini-stats-wid border-bottom border-primary border-3">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium">Total Exceptions</p>
                                <h4 class="mb-0">{{ $totalExceptions }}</h4>
                                <p class="text-muted mt-2 mb-0">
                                    <span class="badge bg-primary-subtle text-primary">
                                        {{ $avgResolutionDays }} avg resolution days
                                    </span>
                                </p>
                            </div>
                            <div class="flex-shrink-0 align-self-center">
                                <div class="avatar-sm rounded-circle bg-primary mini-stat-icon">
                                    <span class="avatar-title rounded-circle bg-primary">
                                        <i class="bx bx-error font-size-24"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card mini-stats-wid border-bottom border-success border-3">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium">Resolved</p>
                                <h4 class="mb-0">{{ $resolvedCount }}</h4>
                                <p class="text-muted mt-2 mb-0">
                                    <span class="badge bg-success-subtle text-success">
                                        {{ $slaComplianceRate }}% SLA Compliance
                                    </span>
                                </p>
                            </div>
                            <div class="flex-shrink-0 align-self-center">
                                <div class="avatar-sm rounded-circle bg-success mini-stat-icon">
                                    <span class="avatar-title rounded-circle bg-success">
                                        <i class="bx bx-check-circle font-size-24"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card mini-stats-wid border-bottom border-warning border-3">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium">Pending</p>
                                <h4 class="mb-0">{{ $pendingCount }}</h4>
                                <p class="text-muted mt-2 mb-0">
                                    <span class="badge bg-warning-subtle text-warning">
                                        {{ $highRiskExceptions->count() }} High Risk
                                    </span>
                                </p>
                            </div>
                            <div class="flex-shrink-0 align-self-center">
                                <div class="avatar-sm rounded-circle bg-warning mini-stat-icon">
                                    <span class="avatar-title rounded-circle bg-warning">
                                        <i class="bx bx-time-five font-size-24"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- end row -->

        <!-- Charts Row -->
        <div class="row">
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Exception Status</h4>
                        <div class="chart-container" style="height: 300px;">
                            <canvas id="statusChart"></canvas>
                        </div>
                        <div class="mt-3 text-center small">
                            @foreach ($statusData as $status => $count)
                                <span class="me-3">
                                    <i
                                        class="fas fa-circle text-{{ $status === 'RESOLVED' ? 'success' : 'warning' }}"></i>
                                    {{ $status }} ({{ $count }})
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Risk Level Distribution</h4>
                        <div class="chart-container" style="height: 300px;">
                            <canvas id="riskChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- end row -->

        <div class="row">
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Exceptions by Department</h4>
                        <div class="chart-container" style="height: 300px;">
                            <canvas id="departmentChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Process Types</h4>
                        <div class="chart-container" style="height: 300px;">
                            <canvas id="processTypeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- end row -->

        <!-- High Risk Exceptions -->
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">
                            <i class="bx bx-error-circle text-danger"></i> High Risk Exceptions
                        </h4>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Exception</th>
                                        <th>Department</th>
                                        <th>Status</th>
                                        <th>Due Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($highRiskExceptions as $exception)
                                        <tr>
                                            <td>{{ Str::limit($exception->exception, 40) }}</td>
                                            <td>{{ $exception->department }}</td>
                                            <td>
                                                <span
                                                    class="badge bg-{{ $exception->status === 'RESOLVED' ? 'success' : 'warning' }}">
                                                    {{ $exception->status }}
                                                </span>
                                            </td>
                                            <td>
                                                @if ($exception->proposeResolutionDate)
                                                    {{ \Carbon\Carbon::parse($exception->proposeResolutionDate)->format('M d, Y') }}
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">No high risk exceptions
                                                found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <!-- end row -->

        @push('scripts')
            <script>
                $(document).ready(function() {
                    // Status Chart - Using Bootstrap colors
                    new Chart(document.getElementById('statusChart'), {
                        type: 'doughnut',
                        data: {
                            labels: {!! json_encode($statusData->keys()) !!},
                            datasets: [{
                                data: {!! json_encode($statusData->values()) !!},
                                backgroundColor: [
                                    '#198754', // RESOLVED - Bootstrap success
                                    '#ffc107' // PENDING - Bootstrap warning
                                ],
                                hoverBorderColor: "rgba(234, 236, 244, 1)",
                            }],
                        },
                        options: {
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false,
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.label || '';
                                            let value = context.raw || 0;
                                            let total = context.dataset.data.reduce((a, b) => a + b, 0);
                                            let percentage = Math.round((value / total) * 100);
                                            return `${label}: ${value} (${percentage}%)`;
                                        }
                                    }
                                }
                            }
                        }
                    });

                    // Risk Chart - Using Bootstrap colors
                    new Chart(document.getElementById('riskChart'), {
                        type: 'bar',
                        data: {
                            labels: {!! json_encode($riskData->keys()) !!},
                            datasets: [{
                                label: "Exceptions",
                                backgroundColor: [
                                    '#198754', // Low - Bootstrap danger
                                    '#dc3545', // High - Bootstrap warning
                                    '#ffc107' // Medium - Bootstrap success
                                ],
                                data: {!! json_encode($riskData->values()) !!},
                            }],
                        },
                        options: {
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        precision: 0
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                }
                            }
                        }
                    });

                    // Department Chart
                    new Chart(document.getElementById('departmentChart'), {
                        type: 'bar',
                        data: {
                            labels: {!! json_encode($departmentData->keys()) !!},
                            datasets: [{
                                label: "Exceptions",
                                backgroundColor: '#0d6efd', // Bootstrap primary
                                data: {!! json_encode($departmentData->values()) !!},
                            }],
                        },
                        options: {
                            indexAxis: 'y',
                            maintainAspectRatio: false,
                            scales: {
                                x: {
                                    beginAtZero: true,
                                    ticks: {
                                        precision: 0
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    display: false
                                }
                            }
                        }
                    });

                    // Process Type Chart
                    new Chart(document.getElementById('processTypeChart'), {
                        type: 'pie',
                        data: {
                            labels: {!! json_encode($processTypeData->keys()) !!},
                            datasets: [{
                                data: {!! json_encode($processTypeData->values()) !!},
                                backgroundColor: [
                                    '#0d6efd', // Bootstrap primary
                                    '#6c757d', // Bootstrap secondary
                                    '#198754', // Bootstrap success
                                    '#ffc107', // Bootstrap warning
                                    '#dc3545' // Bootstrap danger
                                ],
                            }]
                        },
                        options: {
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'right',
                                }
                            }
                        }
                    });
                });
            </script>
        @endpush
    </div>
</x-base-layout>
