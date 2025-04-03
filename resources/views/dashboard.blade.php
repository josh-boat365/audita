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
                    <h4 class="mb-sm-0 font-size-18">Exception Management Dashboard</h4>
                    <div class="text-end">
                        <span class="badge bg-primary">
                            Last Updated: {{ now()->format('M d, Y h:i A') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <!-- Summary Cards -->
        <div class="row">
            <!-- Total Exceptions -->
            <div class="col-md-3">
                <div class="card mini-stats-wid border-bottom border-primary border-3">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium">Total Exceptions</p>
                                <h4 class="mb-0">{{ $totalExceptions }}</h4>
                                <p class="text-muted mt-2 mb-0">
                                    <span class="badge bg-primary-subtle text-primary">
                                        {{ $summaryStats['newThisWeek'] }} new this week
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

            <!-- Resolved -->
            <div class="col-md-3">
                <div class="card mini-stats-wid border-bottom border-success border-3">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium">Resolved</p>
                                <h4 class="mb-0">{{ $resolvedCount }}</h4>
                                <p class="text-muted mt-2 mb-0">
                                    <span class="badge bg-success-subtle text-success">
                                        {{ $slaMetrics['compliance_rate'] }}% SLA Compliance
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

            <!-- Pending -->
            <div class="col-md-3">
                <div class="card mini-stats-wid border-bottom border-warning border-3">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium">Pending</p>
                                <h4 class="mb-0">{{ $pendingCount }}</h4>
                                <p class="text-muted mt-2 mb-0">
                                    <span class="badge bg-warning-subtle text-warning">
                                        {{ $summaryStats['agingExceptions'] }} aging
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

            <!-- High Risk -->
            <div class="col-md-3">
                <div class="card mini-stats-wid border-bottom border-danger border-3">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium">High Risk</p>
                                <h4 class="mb-0">{{ $summaryStats['criticalCount'] }}</h4>
                                <p class="text-muted mt-2 mb-0">
                                    <span class="badge bg-danger-subtle text-danger">
                                        {{ $highRiskExceptions->count() }} active
                                    </span>
                                </p>
                            </div>
                            <div class="flex-shrink-0 align-self-center">
                                <div class="avatar-sm rounded-circle bg-danger mini-stat-icon">
                                    <span class="avatar-title rounded-circle bg-danger">
                                        <i class="bx bx-error-circle font-size-24"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- end row -->

        <!-- Charts Row 1 -->
        <div class="row">
            <!-- Status Distribution -->
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Status Distribution</h4>
                        <div class="chart-container" style="height: 250px;">
                            <canvas id="statusChart"></canvas>
                        </div>
                        <div class="mt-3 text-center small">
                            @foreach ($statusData as $status => $data)
                                <span class="me-3">
                                    <i
                                        class="fas fa-circle text-{{ $status === 'RESOLVED' ? 'success' : 'warning' }}"></i>
                                    {{ $status }} ({{ $data['count'] }})
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Risk Level Distribution -->
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Risk Level Distribution</h4>
                        <div class="chart-container" style="height: 250px;">
                            <canvas id="riskChart"></canvas>
                        </div>
                        <div class="mt-3 text-center small">
                            @foreach ($riskData as $risk => $data)
                                <span class="me-3">
                                    <i class="fas fa-circle" style="color: {{ $riskColors[$risk] }}"></i>
                                    {{ $risk }} ({{ $data['count'] }})
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Trends -->
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Monthly Trends</h4>
                        <div class="chart-container" style="height: 250px;">
                            <canvas id="monthlyTrendsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- end row -->

        <!-- Charts Row 2 -->
        <div class="row">
            <!-- Department Distribution -->
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Department Distribution</h4>
                        <div class="chart-container" style="height: 300px;">
                            <canvas id="departmentChart"></canvas>
                        </div>
                        <div class="mt-3 small">
                            @foreach ($departmentData as $dept => $data)
                                <div class="d-flex justify-content-between mb-2">
                                    <div>
                                        <span class="me-2">{{ $dept }}</span>
                                        <span class="badge bg-primary">{{ $data['count'] }}</span>
                                    </div>
                                    <div>
                                        <span class="text-{{ $data['trend'] > 0 ? 'danger' : 'success' }}">
                                            {{ $data['trend'] > 0 ? '+' : '' }}{{ $data['trend'] }}%
                                            <i class="fas fa-arrow-{{ $data['trend'] > 0 ? 'up' : 'down' }}"></i>
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Process Type Distribution -->
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Process Types</h4>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="chart-container" style="height: 250px;">
                                    <canvas id="processTypeChart"></canvas>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mt-4">
                                @foreach ($processTypeData as $type => $data)
                                    <div class="d-flex justify-content-between mb-3">
                                        <div>
                                            <span class="me-2">{{ $type }}</span>
                                            <span class="badge bg-info">{{ $data['count'] }}</span>
                                        </div>
                                        <div>
                                            <span
                                                class="badge bg-{{ $data['resolution_rate'] > 80 ? 'success' : ($data['resolution_rate'] > 50 ? 'warning' : 'danger') }}">
                                                {{ $data['resolution_rate'] }}% resolved
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
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
                                        <th>Process Type</th>
                                        <th>Status</th>
                                        <th>Days Open</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($highRiskExceptions as $exception)
                                        <tr>
                                            <td>{{ Str::limit($exception['exception'], 40) }}</td>
                                            <td>{{ $exception['department'] }}</td>
                                            <td>{{ $exception['processType'] }}</td>
                                            <td>
                                                <span
                                                    class="badge bg-{{ $exception['status'] === 'RESOLVED' ? 'success' : 'warning' }}">
                                                    {{ $exception['status'] }}
                                                </span>
                                            </td>
                                            <td>
                                                @if ($exception['status'] === 'RESOLVED' && $exception['resolutionTime'])
                                                    {{ max(0, round($exception['resolutionTime'])) }} days
                                                @else
                                                    {{ round(\Carbon\Carbon::parse($exception['occurrenceDate'])->diffInDays(now())) }}
                                                    days
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">No high risk exceptions
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

        <!-- Resolution Time Analysis -->
        <div class="row">
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Resolution Time Analysis</h4>
                        <div class="chart-container" style="height: 300px;">
                            <canvas id="resolutionTimeChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Auditor Performance -->
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Top Auditors</h4>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Auditor</th>
                                        <th>Exceptions</th>
                                        <th>Resolved</th>
                                        <th>Resolution Rate</th>
                                        <th>Avg. Days</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($topAuditors as $auditor => $data)
                                        <tr>
                                            <td>{{ $auditor }}</td>
                                            <td>{{ $data['count'] }}</td>
                                            <td>{{ $data['resolved'] }}</td>
                                            <td>
                                                <div class="progress" style="height: 6px;">
                                                    <div class="progress-bar bg-{{ $data['resolution_rate'] > 80 ? 'success' : ($data['resolution_rate'] > 50 ? 'warning' : 'danger') }}"
                                                        role="progressbar"
                                                        style="width: {{ $data['resolution_rate'] }}%">
                                                    </div>
                                                </div>
                                                <small class="text-muted">{{ $data['resolution_rate'] }}%</small>
                                            </td>
                                            <td>{{ max(0, round(abs($data['avg_resolution']))) }} days</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">No auditor data
                                                available</td>
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
                    // Status Chart
                    new Chart(document.getElementById('statusChart'), {
                        type: 'doughnut',
                        data: {
                            labels: {!! json_encode($statusData->keys()) !!},
                            datasets: [{
                                data: {!! json_encode($statusData->pluck('count')) !!},
                                backgroundColor: ['#198754', '#ffc107'],
                                hoverBorderColor: "rgba(234, 236, 244, 1)",
                            }],
                        },
                        options: {
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            let label = context.label || '';
                                            let value = context.raw || 0;
                                            let percentage = {!! json_encode($statusData->pluck('percentage')) !!}[context.dataIndex];
                                            return `${label}: ${value} (${percentage}%)`;
                                        }
                                    }
                                }
                            }
                        }
                    });

                    // Risk Chart
                    new Chart(document.getElementById('riskChart'), {
                        type: 'bar',
                        data: {
                            labels: {!! json_encode($riskData->keys()) !!},
                            datasets: [{
                                label: "Count",
                                data: {!! json_encode($riskData->pluck('count')) !!},
                                backgroundColor: {!! json_encode($riskColors->values()) !!},
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

                    // Monthly Trends Chart
                    new Chart(document.getElementById('monthlyTrendsChart'), {
                        type: 'line',
                        data: {
                            labels: {!! json_encode($monthlyTrends->pluck('month')) !!},
                            datasets: [{
                                label: 'Total Exceptions',
                                data: {!! json_encode($monthlyTrends->pluck('count')) !!},
                                borderColor: '#0d6efd',
                                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                                tension: 0.3,
                                fill: true
                            }, {
                                label: 'Resolved',
                                data: {!! json_encode($monthlyTrends->pluck('resolved')) !!},
                                borderColor: '#198754',
                                backgroundColor: 'rgba(25, 135, 84, 0.1)',
                                tension: 0.3,
                                fill: true
                            }]
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
                                data: {!! json_encode($departmentData->pluck('count')) !!},
                                backgroundColor: '#0d6efd',
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
                                data: {!! json_encode($processTypeData->pluck('count')) !!},
                                backgroundColor: ['#0d6efd', '#6c757d', '#198754', '#ffc107', '#dc3545'],
                            }]
                        },
                        options: {
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'right'
                                }
                            }
                        }
                    });

                    // Resolution Time Chart
                    new Chart(document.getElementById('resolutionTimeChart'), {
                        type: 'bar',
                        data: {
                            labels: {!! json_encode(array_keys($resolutionBuckets)) !!},
                            datasets: [{
                                label: 'Number of Exceptions',
                                data: {!! json_encode(array_values($resolutionBuckets)) !!},
                                backgroundColor: [
                                    '#198754', '#28a745', '#ffc107', '#fd7e14', '#dc3545'
                                ],
                            }]
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
                });
            </script>
        @endpush
    </div>
</x-base-layout>
