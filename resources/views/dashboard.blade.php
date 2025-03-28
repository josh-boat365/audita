<x-base-layout>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Exception Dashboard</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <!-- Summary Cards -->
        <div class="row">
            <div class="col-md-4">
                <div class="card mini-stats-wid">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium">Total Exceptions</p>
                                <h4 class="mb-0">{{ $totalExceptions }}</h4>
                            </div>
                            <div class="flex-shrink-0 align-self-center">
                                <div class="mini-stat-icon avatar-sm rounded-circle bg-primary">
                                    <span class="avatar-title">
                                        <i class="bx bx-error font-size-24"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card mini-stats-wid">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium">Resolved</p>
                                <h4 class="mb-0">{{ $resolvedCount }}</h4>
                                <p class="text-muted mt-2 mb-0">
                                    <span class="badge badge-soft-success">{{ $slaComplianceRate }}% SLA
                                        Compliance</span>
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
                <div class="card mini-stats-wid">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted fw-medium">Pending</p>
                                <h4 class="mb-0">{{ $pendingCount }}</h4>
                                <p class="text-muted mt-2 mb-0">
                                    <span class="badge badge-soft-danger">{{ $highRiskExceptions->count() }} High
                                        Risk</span>
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
                        <h4 class="card-title mb-4">Monthly Trends</h4>
                        <div class="chart-container" style="height: 300px;">
                            <canvas id="trendChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- end row -->

        <!-- High Risk Exceptions -->
        <div class="row">
            <div class="col-xl-6">
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
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted">No high risk exceptions
                                                found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Recent Activity</h4>
                        <div class="activity-feed">
                            @forelse($recentActivity as $activity)
                                <div class="d-flex mb-4">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="avatar-xs">
                                            <span
                                                class="avatar-title rounded-circle bg-{{ $activity['type'] === 'resolved' ? 'success' : 'primary' }}-subtle text-{{ $activity['type'] === 'resolved' ? 'success' : 'primary' }} font-size-16">
                                                <i
                                                    class="bx bx-{{ $activity['type'] === 'resolved' ? 'check' : 'error' }}-circle"></i>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">{{ $activity['message'] }}</h6>
                                        <p class="text-muted mb-0">{{ $activity['time'] }}</p>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-muted py-3">
                                    No recent activity found
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- end row -->
        <!-- Dashboard Charts -->
        @push('scripts')
            <script>
                $(document).ready(function() {
                    // Status Chart
                    new Chart(document.getElementById('statusChart'), {
                        type: 'doughnut',
                        data: {
                            labels: {!! json_encode($statusData->keys()) !!},
                            datasets: [{
                                data: {!! json_encode($statusData->values()) !!},
                                backgroundColor: [
                                    '#1cbb8c', // RESOLVED
                                    '#5664d2', // PENDING
                                    '#fcb92c', // IN PROGRESS
                                    '#f34770' // OVERDUE
                                ],
                                hoverBorderColor: "rgba(234, 236, 244, 1)",
                            }],
                        },
                        options: {
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    position: 'right',
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

                    // Risk Chart
                    new Chart(document.getElementById('riskChart'), {
                        type: 'bar',
                        data: {
                            labels: {!! json_encode($riskData->keys()) !!},
                            datasets: [{
                                label: "Exceptions",
                                backgroundColor: [
                                    '#f34770', // High
                                    '#fcb92c', // Medium
                                    '#1cbb8c' // Low
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
                                backgroundColor: '#5664d2',
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

                    // Trend Chart
                    new Chart(document.getElementById('trendChart'), {
                        type: 'line',
                        data: {
                            labels: {!! json_encode($monthlyTrends->keys()) !!},
                            datasets: [{
                                label: "Exceptions",
                                lineTension: 0.3,
                                backgroundColor: "rgba(86, 100, 210, 0.05)",
                                borderColor: "rgba(86, 100, 210, 1)",
                                pointRadius: 3,
                                pointBackgroundColor: "rgba(86, 100, 210, 1)",
                                pointBorderColor: "rgba(86, 100, 210, 1)",
                                pointHoverRadius: 3,
                                pointHoverBackgroundColor: "rgba(86, 100, 210, 1)",
                                pointHoverBorderColor: "rgba(86, 100, 210, 1)",
                                pointHitRadius: 10,
                                pointBorderWidth: 2,
                                data: {!! json_encode($monthlyTrends->values()) !!},
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
                });
            </script>
        @endpush
    </div>


</x-base-layout>
