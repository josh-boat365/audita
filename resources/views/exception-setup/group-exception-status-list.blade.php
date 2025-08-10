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
                                <div class="col-md-3">
                                    <label for="batchFilter" class="form-label">Batch</label>
                                    <select id="batchFilter" class="form-select">
                                        <option value="">All Batches</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="branchFilter" class="form-label">Branch</label>
                                    <select id="branchFilter" class="form-select">
                                        <option value="">All Branches</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="statusFilter" class="form-label">Status</label>
                                    <select id="statusFilter" class="form-select">
                                        <option value="">All Statuses</option>
                                        <option value="APPROVED">APPROVED</option>
                                        <option value="AMENDMENT">AMENDMENT</option>
                                        <option value="ANALYSIS">ANALYSIS</option>
                                        <option value="RESOLVED">RESOLVED</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="searchFilter" class="form-label">Global Search</label>
                                    <input type="text" id="searchFilter" class="form-control"
                                        placeholder="Search batch code, name, branch...">
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

        <!-- Results section - Initially Hidden -->
        <div id="resultsSection" style="display: none;">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Batch Code</th>
                            <th>Batch Name</th>
                            <th>Auditor</th>
                            <th>Branch</th>
                            <th>Department</th>
                            <th>Submission Date</th>
                            <th>Status</th>
                            <th>Exception Count</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="tableBody">
                        <!-- Dynamic content will be loaded here -->
                    </tbody>
                </table>

                <!-- Pagination container -->
                <div id="paginationContainer" class="mt-3"></div>
            </div>
        </div>

        <!-- Initial message -->
        <div id="initialMessage" class="text-center py-5">
            <i class="bx bx-filter fs-1 text-muted"></i>
            <h5 class="text-muted mt-3">Use the filters above to search for exceptions</h5>
            <p class="text-muted">Select your criteria and click "Apply Filters" to view results</p>
        </div>

        <!-- No Results message -->
        <div id="noResultsMessage" class="text-center py-5" style="display: none;">
            <i class="bx bx-search fs-1 text-muted"></i>
            <h5 class="text-muted mt-3">No exceptions match your filter criteria</h5>
            <p class="text-muted">Try adjusting your filters or search terms</p>
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
                            console.log('Filter options response:', response);

                            // Populate batch dropdown
                            const batchSelect = $('#batchFilter');
                            batchSelect.find('option:not(:first)').remove();
                            if (response.batches && response.batches.length > 0) {
                                response.batches.forEach(batch => {
                                    batchSelect.append(
                                    `<option value="${batch}">${batch}</option>`);
                                });
                            }

                            // Populate branch dropdown
                            const branchSelect = $('#branchFilter');
                            branchSelect.find('option:not(:first)').remove();
                            if (response.branches && response.branches.length > 0) {
                                response.branches.forEach(branch => {
                                    branchSelect.append(
                                        `<option value="${branch}">${branch}</option>`);
                                });
                            }
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
                        batch: $('#batchFilter').val(),
                        branch: $('#branchFilter').val(),
                        status: $('#statusFilter').val(),
                        search: $('#searchFilter').val(),
                        dateFrom: $('#dateFromFilter').val(),
                        dateTo: $('#dateToFilter').val(),
                        page: page,
                        perPage: perPage
                    };

                    console.log('Applying filters:', filters);

                    // Show loading indicator, hide other sections
                    $('#loadingIndicator').show();
                    $('#resultsSection').hide();
                    $('#initialMessage').hide();
                    $('#noResultsMessage').hide();

                    $.ajax({
                        url: '{{ route('exceptions.filter') }}',
                        method: 'GET',
                        data: filters,
                        success: function(response) {
                            console.log('Filter response:', response);
                            $('#loadingIndicator').hide();

                            if (response.data && response.data.length > 0) {
                                displayResults(response.data);
                                updatePagination(response.pagination);
                                updateResultsInfo(response.summary);
                                $('#resultsSection').show();
                                $('.results-info').show();
                            } else {
                                $('#noResultsMessage').show();
                                $('.results-info').hide();
                            }

                            currentPage = response.pagination ? response.pagination.current_page : 1;
                        },
                        error: function(xhr) {
                            console.error('Filter request failed:', xhr);
                            $('#loadingIndicator').hide();

                            if (xhr.status === 401) {
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

                    data.forEach(batchException => {
                        const row = createTableRow(batchException);
                        tbody.append(row);
                    });
                }

                // Function to create table row
                function createTableRow(batchException) {
                    const statusClass = {
                        'resolved': 'bg-success',
                        'analysis': 'bg-warning',
                        'approved': 'bg-primary',
                        'amendment': 'bg-info'
                    } [batchException.status?.toLowerCase()] || 'bg-secondary';

                    // Format date
                    let formattedDate = 'N/A';
                    if (batchException.submittedAt) {
                        try {
                            const date = new Date(batchException.submittedAt);
                            const day = date.getDate();
                            const suffix = getDaySuffix(day);
                            formattedDate = `${day}${suffix} ${date.toLocaleDateString('en-GB', {
                                month: 'long',
                                year: 'numeric'
                            })}`;
                        } catch (e) {
                            console.warn('Date formatting error:', e);
                        }
                    }

                    // Get batch and branch info
                    const batchCode = batchException.exceptionBatch?.code || 'N/A';
                    const batchName = batchException.exceptionBatch?.name || 'N/A';
                    const branchName = batchException.exceptionBatch?.activityGroupName || 'N/A';
                    const submittedBy = batchException.submittedBy || 'N/A';
                    const departmentName = batchException.departmentName || 'N/A';
                    const status = batchException.status || 'N/A';
                    const exceptionCount = batchException.exceptions?.length || 0;

                    return `
                        <tr>
                            <td><a href="#" class="text-primary">${batchCode}</a></td>
                            <td>${batchName}</td>
                            <td><a href="#" class="text-primary">${submittedBy}</a></td>
                            <td>
                                <span class="badge rounded-pill bg-primary">
                                    ${branchName}
                                </span>
                            </td>
                            <td>${departmentName}</td>
                            <td>${formattedDate}</td>
                            <td>
                                <span class="badge rounded-pill ${statusClass}">
                                    ${status}
                                </span>
                            </td>
                            <td>
                                <span class="badge rounded-pill bg-dark">
                                    ${exceptionCount}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="{{ url('/exception/group-exception-status-open/') }}/${batchException.id}/${batchException.status}">
                                        <span class="badge bg-primary font-size-13">
                                            <i class="bx bx-folder-open"></i> Open
                                        </span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    `;
                }

                // Helper function for date suffix
                function getDaySuffix(day) {
                    if (day >= 11 && day <= 13) return 'th';
                    switch (day % 10) {
                        case 1:
                            return 'st';
                        case 2:
                            return 'nd';
                        case 3:
                            return 'rd';
                        default:
                            return 'th';
                    }
                }

                // Function to update pagination
                function updatePagination(pagination) {
                    const container = $('#paginationContainer');
                    container.empty();

                    if (!pagination || pagination.last_page <= 1) {
                        return;
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
                    if (pagination.from && pagination.to && pagination.total) {
                        paginationHtml += `<div class="text-center mt-2 text-muted">
                            <small>Showing ${pagination.from} to ${pagination.to} of ${pagination.total} results</small>
                        </div>`;
                    }

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
                    $('#initialMessage').hide();
                    $('#noResultsMessage').hide();
                }

                // Event listeners
                $('#applyFilters').on('click', function() {
                    currentPage = 1;
                    applyFilters(1);
                });

                $('#resetFilters').on('click', function() {
                    $('#exportFilters')[0].reset();
                    $('#resultsSection').hide();
                    $('#noResultsMessage').hide();
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
                    if (e.which === 13) {
                        currentPage = 1;
                        applyFilters(1);
                    }
                });

                // Initialize dropdowns on page load
                populateDropdowns();

                // Show initial message on load
                $('#initialMessage').show();
            });
        </script>
    @endpush
</x-base-layout>
