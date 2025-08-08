<x-base-layout>

    <div class="container-fluid px-1">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">List of Exceptions With Their Status For Branch</h4>
                    <div class="results-info" style="display: none;">
                        <span class="badge bg-info results-count">0 results</span>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <!-- Filter Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title mb-4">Filters</h4>
                        <form id="exportFilters">
                            <div class="row">
                                {{--  <div class="col-md-2">
                                    <label for="batchFilter" class="form-label">Batch</label>
                                    <select id="batchFilter" class="form-select">
                                        <option value="">All Batches</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="branchFilter" class="form-label">Branch</label>
                                    <select id="branchFilter" class="form-select">
                                        <option value="">All Branches</option>
                                    </select>
                                </div>  --}}
                                <div class="col-md-2">
                                    <label for="auditorFilter" class="form-label">Auditor</label>
                                    <select id="auditorFilter" class="form-select">
                                        <option value="">All Auditors</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="statusFilter" class="form-label">Status</label>
                                    <select id="statusFilter" class="form-select">
                                        <option value="">All Statuses</option>
                                        <option value="APPROVED">APPROVED</option>
                                        <option value="AMENDMENT">AMENDMENT</option>
                                        <option value="ANALYSIS">ANALYSIS</option>
                                        <option value="RESOLVED">RESOLVED</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="riskRateFilter" class="form-label">Risk Rate</label>
                                    <select id="riskRateFilter" class="form-select">
                                        <option value="">All Risk Rates</option>
                                        <option value="High">High</option>
                                        <option value="Medium">Medium</option>
                                        <option value="Low">Low</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="searchFilter" class="form-label">Search</label>
                                    <input type="text" id="searchFilter" class="form-control"
                                        placeholder="Search exceptions...">
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-3">
                                    <label for="dateFromFilter" class="form-label">From Date</label>
                                    <input type="date" id="dateFromFilter" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label for="dateToFilter" class="form-label">To Date</label>
                                    <input type="date" id="dateToFilter" class="form-control">
                                </div>
                                <div class="col-md-3 d-flex align-items-end gap-2">
                                    <button type="button" id="applyFilters" class="btn btn-primary">
                                        <i class="bx bx-search"></i> Apply Filters
                                    </button>
                                    <button type="button" id="resetFilters" class="btn btn-secondary">
                                        <i class="bx bx-refresh"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 mb-4" style="background-color: gray; height: 1px;"></div>

        <!-- Loading indicator -->
        <div id="loadingIndicator" class="text-center py-4" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Loading exceptions...</p>
        </div>

        <!-- Results section -->
        <div id="resultsSection" style="display: none;">
            <div class="table-responsive">
                <table id="reportsTable" class="table table-bordered table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Exception Title</th>
                            <th>Exception Description</th>
                            <th>Auditor</th>
                            <th>Branch</th>
                            <th>Department</th>
                            <th>Risk Rate</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <!-- Dynamic content will be loaded here -->
                    </tbody>
                </table>

                <!-- Pagination will be handled dynamically -->
                <nav aria-label="Page navigation" class="mt-3" id="paginationContainer">
                    <!-- Dynamic pagination will be inserted here -->
                </nav>
            </div>
        </div>

        <!-- Initial message -->
        <div id="initialMessage" class="text-center py-5">
            <i class="bx bx-filter fs-1 text-muted"></i>
            <h5 class="text-muted mt-3">Use the filters above to search for exceptions</h5>
            <p class="text-muted">Select your criteria and click "Apply Filters" to view results</p>
        </div>

    </div>

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
            $(document).ready(function() {
                let currentPage = 1;
                const perPage = 15;

                // Populate dropdowns on page load
                function populateDropdowns() {
                    $.ajax({
                        url: '{{ route('exceptions.filter-options') }}',
                        method: 'GET',
                        success: function(response) {
                            // Populate branch dropdown
                            const branchSelect = $('#branchFilter');
                            branchSelect.find('option:not(:first)').remove(); // Keep "All Branches" option
                            response.branches.forEach(branch => {
                                branchSelect.append(`<option value="${branch}">${branch}</option>`);
                            });

                            // Populate auditor dropdown
                            const auditorSelect = $('#auditorFilter');
                            auditorSelect.find('option:not(:first)').remove(); // Keep "All Auditors" option
                            response.auditors.forEach(auditor => {
                                auditorSelect.append(
                                    `<option value="${auditor}">${auditor}</option>`);
                            });
                        },
                        error: function(xhr) {
                            console.error('Failed to load filter options:', xhr);
                            if (xhr.status === 401) {
                                window.location.href = '{{ route('login') }}';
                            }
                        }
                    });
                }

                // Function to apply filters and load data
                function applyFilters(page = 1) {
                    const filters = {
                        branch: $('#branchFilter').val(),
                        auditor: $('#auditorFilter').val(),
                        status: $('#statusFilter').val(),
                        riskRate: $('#riskRateFilter').val(),
                        search: $('#searchFilter').val(),
                        dateFrom: $('#dateFromFilter').val(),
                        dateTo: $('#dateToFilter').val(),
                        page: page,
                        perPage: perPage
                    };

                    // Show loading indicator
                    $('#loadingIndicator').show();
                    $('#resultsSection').hide();
                    $('#initialMessage').hide();

                    $.ajax({
                        url: '{{ route('exceptions.filter') }}',
                        method: 'GET',
                        data: filters,
                        success: function(response) {
                            displayResults(response.data);
                            updatePagination(response.pagination);
                            updateResultsInfo(response.summary);

                            $('#loadingIndicator').hide();
                            $('#resultsSection').show();
                            $('.results-info').show();

                            currentPage = response.pagination.current_page;
                        },
                        error: function(xhr) {
                            $('#loadingIndicator').hide();

                            if (xhr.status === 401) {
                                // Session expired
                                window.location.href = '{{ route('login') }}';
                                return;
                            }

                            let errorMessage = 'An error occurred while filtering exceptions.';
                            if (xhr.responseJSON && xhr.responseJSON.error) {
                                errorMessage = xhr.responseJSON.error;
                            }

                            showErrorMessage(errorMessage);
                        }
                    });
                }

                // Function to display results
                function displayResults(data) {
                    const tbody = $('#tableBody');
                    tbody.empty();

                    if (data.length === 0) {
                        tbody.append(`
                <tr>
                    <td colspan="9" class="text-center text-muted py-4">
                        <i class="bx bx-search fs-1 text-muted"></i>
                        <p class="mb-0">No exceptions match your filter criteria</p>
                        <small>Try adjusting your filters</small>
                    </td>
                </tr>
            `);
                        return;
                    }

                    data.forEach(batchException => {
                        if (batchException.exceptions) {
                            batchException.exceptions.forEach(exception => {
                                const row = createTableRow(batchException, exception);
                                tbody.append(row);
                            });
                        }
                    });
                }

                // Function to create table row
                function createTableRow(batchException, exception) {
                    const riskClass = {
                        'high': 'bg-danger',
                        'medium': 'bg-warning',
                        'low': 'bg-primary'
                    } [exception.riskRate?.toLowerCase()] || 'bg-secondary';

                    const statusClass = {
                        'resolved': 'bg-success',
                        'analysis': 'bg-dark',
                        'approved': 'bg-primary',
                        'amendment': 'bg-warning'
                    } [batchException.status?.toLowerCase()] || 'bg-secondary';

                    // Format date
                    let formattedDate = 'N/A';
                    if (batchException.submittedAt) {
                        try {
                            const date = new Date(batchException.submittedAt);
                            formattedDate = date.toLocaleDateString('en-GB', {
                                day: 'numeric',
                                month: 'long',
                                year: 'numeric'
                            });
                        } catch (e) {
                            console.warn('Date formatting error:', e);
                        }
                    }

                    // Get branch name
                    let branchName = 'N/A';
                    if (batchException.exceptionBatch && batchException.exceptionBatch.activityGroupName) {
                        branchName = batchException.exceptionBatch.activityGroupName;
                    }

                    return `
            <tr>
                <th scope="row"><a href="#">${exception.exceptionTitle || 'N/A'}</a></th>
                <th scope="row"><a href="#">${exception.exception || 'N/A'}</a></th>
                <th scope="row"><a href="#">${batchException.submittedBy || 'N/A'}</a></th>
                <td>
                    <span class="dropdown badge rounded-pill bg-primary">
                        ${branchName}
                    </span>
                </td>
                <td>
                    <div>
                        ${batchException.departmentName || 'N/A'}
                        <span class="dropdown badge rounded-pill bg-dark">
                            ${batchException.exceptions?.length || 0}
                        </span>
                    </div>
                </td>
                <td>
                    <span class="badge rounded-pill ${riskClass}">
                        ${exception.riskRate || 'Not Determined'}
                    </span>
                </td>
                <td>${formattedDate}</td>
                <td>
                    <span class="dropdown badge rounded-pill ${statusClass}">
                        ${batchException.status || 'Not Determined'}
                    </span>
                </td>
                <td>
                    <div class="d-flex gap-3">
                        <a href="{{ url('/exception/supervisor/show-exception-list-for-approval') }}/${exception.id}/${exception.status || ''}">
                            <span class="badge round bg-primary font-size-13">
                                <i class="bx bxs-pencil"></i> open
                            </span>
                        </a>
                    </div>
                </td>
            </tr>
        `;
                }

                // Function to update pagination
                function updatePagination(pagination) {
                    const container = $('#paginationContainer');
                    container.empty();

                    if (pagination.last_page <= 1) {
                        return; // No pagination needed
                    }

                    let paginationHtml =
                        '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';

                    // Previous button
                    if (pagination.current_page > 1) {
                        paginationHtml +=
                            `<li class="page-item"><a class="page-link" href="#" data-page="${pagination.current_page - 1}">Previous</a></li>`;
                    } else {
                        paginationHtml += '<li class="page-item disabled"><span class="page-link">Previous</span></li>';
                    }

                    // Page numbers
                    const startPage = Math.max(1, pagination.current_page - 2);
                    const endPage = Math.min(pagination.last_page, pagination.current_page + 2);

                    if (startPage > 1) {
                        paginationHtml +=
                            '<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>';
                        if (startPage > 2) {
                            paginationHtml += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                    }

                    for (let i = startPage; i <= endPage; i++) {
                        if (i === pagination.current_page) {
                            paginationHtml += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
                        } else {
                            paginationHtml +=
                                `<li class="page-item"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
                        }
                    }

                    if (endPage < pagination.last_page) {
                        if (endPage < pagination.last_page - 1) {
                            paginationHtml += '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                        paginationHtml +=
                            `<li class="page-item"><a class="page-link" href="#" data-page="${pagination.last_page}">${pagination.last_page}</a></li>`;
                    }

                    // Next button
                    if (pagination.current_page < pagination.last_page) {
                        paginationHtml +=
                            `<li class="page-item"><a class="page-link" href="#" data-page="${pagination.current_page + 1}">Next</a></li>`;
                    } else {
                        paginationHtml += '<li class="page-item disabled"><span class="page-link">Next</span></li>';
                    }

                    paginationHtml += '</ul></nav>';

                    // Add pagination info
                    paginationHtml += `<div class="text-center mt-2 text-muted">
            <small>Showing ${pagination.from} to ${pagination.to} of ${pagination.total} results</small>
        </div>`;

                    container.html(paginationHtml);
                }

                // Function to update results info
                function updateResultsInfo(summary) {
                    if (summary) {
                        $('.results-count').text(`${summary.showing} of ${summary.total_exceptions} results`);
                    }
                }

                // Function to show error message
                function showErrorMessage(message) {
                    const tbody = $('#tableBody');
                    tbody.html(`
            <tr>
                <td colspan="9" class="text-center text-danger py-4">
                    <i class="bx bx-error fs-1 text-danger"></i>
                    <p class="mb-0">${message}</p>
                    <small>Please try again or contact support if the problem persists</small>
                </td>
            </tr>
        `);

                    $('#resultsSection').show();
                    $('.results-info').hide();
                }

                // Event listeners
                $('#applyFilters').on('click', function() {
                    currentPage = 1; // Reset to first page when applying new filters
                    applyFilters(1);
                });

                $('#resetFilters').on('click', function() {
                    // Reset all form fields
                    $('#exportFilters')[0].reset();

                    // Hide results and show initial message
                    $('#resultsSection').hide();
                    $('#initialMessage').show();
                    $('.results-info').hide();

                    currentPage = 1;
                });

                // Pagination click handler
                $(document).on('click', '.page-link[data-page]', function(e) {
                    e.preventDefault();
                    const page = $(this).data('page');
                    applyFilters(page);
                });

                // Enter key support for search
                $('#searchFilter').on('keypress', function(e) {
                    if (e.which === 13) { // Enter key
                        currentPage = 1;
                        applyFilters(1);
                    }
                });

                // Auto-apply filters when dropdowns change (optional)
                $('#branchFilter, #auditorFilter, #statusFilter, #riskRateFilter').on('change', function() {
                    // Uncomment below for auto-filtering on dropdown change
                    // currentPage = 1;
                    // applyFilters(1);
                });

                // Initialize dropdowns on page load
                populateDropdowns();
            });
        </script>
    @endpush

</x-base-layout>
