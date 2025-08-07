<x-base-layout>

    <div class="container-fluid px-1">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">List of Exceptions With Their Status For Branch</h4>
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

                                <div class="col-md-2">
                                    <label for="branchFilter" class="form-label">Branch</label>
                                    <select id="branchFilter" class="form-select">
                                        <option value="">All Branches</option>

                                    </select>
                                </div>
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

                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="riskRateFilter" class="form-label">Risk Rate</label>
                                    <select id="riskRateFilter" class="form-select">
                                        <option value="">All Risk Rates</option>

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

        <div class="mt-4 mb-4" style="background-color: gray; height: 1px;"></div>

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
                <tbody>
                    {{--  {{ dd($pendingExceptions) }}  --}}
                    @forelse ($pendingExceptions as $batchException)
                        @foreach ($batchException['exceptions'] as $exception)
                            <tr>
                                <th scope="row"><a href="#">{{ $exception['exceptionTitle'] }}</a></th>
                                <th scope="row"><a href="#">{{ $exception['exception'] }}</a></th>
                                <th scope="row"><a href="#">{{ $batchException['submittedBy'] }}</a></th>

                                <td>
                                    <span class="dropdown badge rounded-pill bg-primary">
                                        {{ $batchException['exceptionBatch']['activityGroupName'] }}
                                    </span>
                                </td>
                                <td>
                                    <div>
                                        {{ $batchException['departmentName'] }}
                                        <span class="dropdown badge rounded-pill bg-dark">
                                            {{ count($batchException['exceptions']) ?? '------' }}
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $riskClass = match (strtolower($exception['riskRate'])) {
                                            'high' => 'bg-danger',
                                            'medium' => 'bg-warning',
                                            'low' => 'bg-primary',
                                            default => 'bg-secondary',
                                        };
                                    @endphp

                                    <span class="badge rounded-pill {{ $riskClass }}">
                                        {{ $exception['riskRate'] ?? 'Not Determined' }}
                                    </span>
                                </td>
                                <td> {{ Carbon\Carbon::parse($batchException['submittedAt'])->format('jS F, Y ') }}
                                </td>
                                <td>
                                    @php
                                        $statusClass = match (strtolower($batchException['status'])) {
                                            'resolved' => 'bg-success',
                                            'analysis' => 'bg-dark',
                                            'approved' => 'bg-primary',
                                            'amendment' => 'bg-warning',
                                            default => 'bg-secondary',
                                        };
                                    @endphp <span class="dropdown badge rounded-pill {{ $statusClass }} ">
                                        {{ $batchException['status'] ?? 'Not Determined' }}
                                    </span>
                                </td>

                                <td>
                                    <div class="d-flex gap-3">
                                        <a
                                            href="{{ url("/exception/supervisor/show-exception-list-for-approval/{$exception['id']}/{$exception['status']}") }}">
                                            <span class="badge round bg-primary font-size-13"><i
                                                    class="bx bxs-pencil"></i>open</span>
                                        </a>

                                    </div>
                                </td>
                            </tr>
                        @endforeach

                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bx bx-file fs-1 text-muted"></i>
                                <p class="mb-0">No pending exceptions for <b>RESPONSE</b> from <b>AUDITOR</b></p>
                                <small>All exceptions have been processed</small>
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
            // AJAX Frontend Table Filtering
            $(document).ready(function() {

                // Populate filter dropdowns from existing table data
                function populateFilters() {
                    const branches = new Set();
                    const auditors = new Set();
                    const statuses = new Set();
                    const riskRates = new Set();

                    // Extract unique values from table
                    $('#reportsTable tbody tr').each(function() {
                        const row = $(this);

                        // Skip empty state rows
                        if (row.find('td[colspan]').length > 0) return;

                        // Extract values
                        const auditor = row.find('th:eq(2)').text().trim();
                        const branch = row.find('td:eq(0) .badge').text().trim();
                        const riskRate = row.find('td:eq(2) .badge').text().trim();
                        const status = row.find('td:eq(4) .badge').text().trim();

                        if (auditor && auditor !== '') auditors.add(auditor);
                        if (branch && branch !== '') branches.add(branch);
                        if (riskRate && riskRate !== '' && riskRate !== 'Not Determined') riskRates.add(
                            riskRate);
                        if (status && status !== '' && status !== 'Not Determined') statuses.add(status);
                    });

                    // Populate dropdowns
                    populateDropdown('#branchFilter', branches);
                    populateDropdown('#auditorFilter', auditors);
                    populateDropdown('#statusFilter', statuses);
                    populateDropdown('#riskRateFilter', riskRates);
                }

                // Helper function to populate dropdown
                function populateDropdown(selector, values) {
                    const dropdown = $(selector);
                    const sortedValues = Array.from(values).sort();

                    sortedValues.forEach(value => {
                        dropdown.append(`<option value="${value}">${value}</option>`);
                    });
                }

                // Function to filter table rows
                function filterTable() {
                    const branchFilter = $('#branchFilter').val().toLowerCase();
                    const auditorFilter = $('#auditorFilter').val().toLowerCase();
                    const statusFilter = $('#statusFilter').val().toLowerCase();
                    const riskRateFilter = $('#riskRateFilter').val().toLowerCase();
                    const dateFromFilter = $('#dateFromFilter').val();
                    const dateToFilter = $('#dateToFilter').val();

                    let visibleRows = 0;

                    // Loop through each table row (skip header)
                    $('#reportsTable tbody tr').each(function() {
                        const row = $(this);

                        // Skip if it's an empty state row
                        if (row.find('td[colspan]').length > 0) {
                            return;
                        }

                        // Get cell values (adjusted for new structure)
                        const auditor = row.find('th:eq(2)').text().toLowerCase()
                    .trim(); // 3rd column (Auditor)
                        const branch = row.find('td:eq(0) .badge').text().toLowerCase()
                    .trim(); // Branch name in badge
                        const status = row.find('td:eq(4) .badge').text().toLowerCase().trim(); // Status badge
                        const riskRate = row.find('td:eq(2) .badge').text().toLowerCase()
                    .trim(); // Risk rate badge
                        const dateText = row.find('td:eq(3)').text().trim(); // Date column

                        // Check filters
                        let showRow = true;

                        // Branch filter
                        if (branchFilter && !branch.includes(branchFilter)) {
                            showRow = false;
                        }

                        // Auditor filter
                        if (auditorFilter && !auditor.includes(auditorFilter)) {
                            showRow = false;
                        }

                        // Status filter
                        if (statusFilter && !status.includes(statusFilter)) {
                            showRow = false;
                        }

                        // Risk rate filter
                        if (riskRateFilter && !riskRate.includes(riskRateFilter)) {
                            showRow = false;
                        }

                        // Date range filter
                        if (dateFromFilter || dateToFilter) {
                            const rowDate = parseDateFromText(dateText);
                            if (rowDate) {
                                const fromDate = dateFromFilter ? new Date(dateFromFilter) : null;
                                const toDate = dateToFilter ? new Date(dateToFilter) : null;

                                if (fromDate && rowDate < fromDate) showRow = false;
                                if (toDate && rowDate > toDate) showRow = false;
                            }
                        }

                        // Show/hide row
                        if (showRow) {
                            row.show();
                            visibleRows++;
                        } else {
                            row.hide();
                        }
                    });

                    // Handle empty state
                    handleEmptyState(visibleRows);

                    // Update results count
                    updateResultsCount(visibleRows);
                }

                // Helper function to parse date from table text
                function parseDateFromText(dateText) {
                    try {
                        // Assuming format like "19th June, 2025"
                        const cleanText = dateText.trim().replace(/(\d+)(st|nd|rd|th)/, '$1');
                        return new Date(cleanText);
                    } catch (e) {
                        return null;
                    }
                }

                // Handle empty state message
                function handleEmptyState(visibleRows) {
                    const tbody = $('#reportsTable tbody');
                    const emptyRow = tbody.find('.empty-state-row');

                    if (visibleRows === 0) {
                        if (emptyRow.length === 0) {
                            tbody.append(`
                    <tr class="empty-state-row">
                        <td colspan="9" class="text-center text-muted py-4">
                            <i class="bx bx-search fs-1 text-muted"></i>
                            <p class="mb-0">No exceptions match your filter criteria</p>
                            <small>Try adjusting your filters</small>
                        </td>
                    </tr>
                `);
                        }
                    } else {
                        emptyRow.remove();
                    }
                }

                // Update results count
                function updateResultsCount(count) {
                    // Remove existing count badge
                    $('.results-count').remove();

                    // Add new count badge
                    $('.page-title-box h4').append(`
            <span class="badge bg-info ms-2 results-count">${count} shown</span>
        `);
                }

                // Event listeners for all filters
                $('#branchFilter, #auditorFilter, #statusFilter, #riskRateFilter').on('change', function() {
                    filterTable();
                });

                // Date filter listeners
                $('#dateFromFilter, #dateToFilter').on('change', function() {
                    filterTable();
                });

                // Reset filters
                $('#resetFilters').on('click', function() {
                    $('#branchFilter, #auditorFilter, #statusFilter, #riskRateFilter').val('');
                    $('#dateFromFilter, #dateToFilter').val('');

                    // Show all rows
                    $('#reportsTable tbody tr').show();
                    $('.empty-state-row').remove();
                    $('.results-count').remove();
                });

                // Initialize filters on page load
                populateFilters();

                // Real-time search functionality (optional)
                function addSearchBox() {
                    const searchHtml = `
            <div class="col-md-3">
                <label for="searchFilter" class="form-label">Search</label>
                <input type="text" id="searchFilter" class="form-control"
                       placeholder="Search exceptions...">
            </div>
        `;

                    // Add search box to the first row of filters
                    $('.row:first', '#exportFilters').append(searchHtml);

                    // Search functionality
                    $('#searchFilter').on('keyup', function() {
                        const searchTerm = $(this).val().toLowerCase();

                        $('table tbody tr').each(function() {
                            const row = $(this);
                            const text = row.text().toLowerCase();

                            if (text.includes(searchTerm) || searchTerm === '') {
                                row.show();
                            } else {
                                row.hide();
                            }
                        });
                    });
                }

                // Initialize search box (uncomment if needed)
                // addSearchBox();

                // Filter by clicking on badges (optional enhancement)
                $(document).on('click', '.badge', function(e) {
                    e.preventDefault();
                    const badgeText = $(this).text().trim();
                    const badgeParent = $(this).closest('td, th');
                    const columnIndex = badgeParent.index();

                    // Determine which filter to set based on the column index
                    if (columnIndex === 3) { // Branch column (4th column, 0-indexed = 3)
                        $('#branchFilter').val(badgeText);
                    } else if (columnIndex === 5) { // Risk rate column (6th column, 0-indexed = 5)
                        if (badgeText !== 'Not Determined') {
                            $('#riskRateFilter').val(badgeText);
                        }
                    } else if (columnIndex === 7) { // Status column (8th column, 0-indexed = 7)
                        if (badgeText !== 'Not Determined') {
                            $('#statusFilter').val(badgeText);
                        }
                    }

                    filterTable();
                });

            });

            // Pure JavaScript version (if not using jQuery)
            document.addEventListener('DOMContentLoaded', function() {

                // Populate filter dropdowns from existing table data
                function populateFiltersVanilla() {
                    const branches = new Set();
                    const auditors = new Set();
                    const statuses = new Set();
                    const riskRates = new Set();

                    const rows = document.querySelectorAll('#reportsTable tbody tr');

                    rows.forEach(row => {
                        // Skip empty state rows
                        if (row.querySelector('td[colspan]')) return;

                        const auditor = row.cells[2]?.textContent.trim();
                        const branchBadge = row.cells[3]?.querySelector('.badge');
                        const riskRateBadge = row.cells[5]?.querySelector('.badge');
                        const statusBadge = row.cells[7]?.querySelector('.badge');

                        if (auditor) auditors.add(auditor);
                        if (branchBadge?.textContent.trim()) branches.add(branchBadge.textContent.trim());
                        if (riskRateBadge?.textContent.trim() && riskRateBadge.textContent.trim() !==
                            'Not Determined') {
                            riskRates.add(riskRateBadge.textContent.trim());
                        }
                        if (statusBadge?.textContent.trim() && statusBadge.textContent.trim() !==
                            'Not Determined') {
                            statuses.add(statusBadge.textContent.trim());
                        }
                    });

                    // Populate dropdowns
                    populateDropdownVanilla('branchFilter', branches);
                    populateDropdownVanilla('auditorFilter', auditors);
                    populateDropdownVanilla('statusFilter', statuses);
                    populateDropdownVanilla('riskRateFilter', riskRates);
                }

                function populateDropdownVanilla(id, values) {
                    const dropdown = document.getElementById(id);
                    const sortedValues = Array.from(values).sort();

                    sortedValues.forEach(value => {
                        const option = document.createElement('option');
                        option.value = value;
                        option.textContent = value;
                        dropdown.appendChild(option);
                    });
                }

                function filterTableVanilla() {
                    const branchFilter = document.getElementById('branchFilter').value.toLowerCase();
                    const auditorFilter = document.getElementById('auditorFilter').value.toLowerCase();
                    const statusFilter = document.getElementById('statusFilter').value.toLowerCase();
                    const riskRateFilter = document.getElementById('riskRateFilter').value.toLowerCase();

                    const rows = document.querySelectorAll('#reportsTable tbody tr');
                    let visibleCount = 0;

                    rows.forEach(row => {
                        // Skip if it's a colspan row (empty state)
                        if (row.querySelector('td[colspan]')) return;

                        const auditor = row.cells[2]?.textContent.toLowerCase().trim() || '';
                        const branch = row.cells[3]?.querySelector('.badge')?.textContent.toLowerCase()
                        .trim() || '';
                        const riskRate = row.cells[5]?.querySelector('.badge')?.textContent.toLowerCase()
                        .trim() || '';
                        const status = row.cells[7]?.querySelector('.badge')?.textContent.toLowerCase()
                        .trim() || '';

                        const matchesBranch = !branchFilter || branch.includes(branchFilter);
                        const matchesAuditor = !auditorFilter || auditor.includes(auditorFilter);
                        const matchesStatus = !statusFilter || status.includes(statusFilter);
                        const matchesRiskRate = !riskRateFilter || riskRate.includes(riskRateFilter);

                        if (matchesBranch && matchesAuditor && matchesStatus && matchesRiskRate) {
                            row.style.display = '';
                            visibleCount++;
                        } else {
                            row.style.display = 'none';
                        }
                    });

                    console.log(`Filtered: ${visibleCount} rows visible`);

                    // Handle empty state
                    handleEmptyStateVanilla(visibleCount);
                }

                function handleEmptyStateVanilla(visibleCount) {
                    const tbody = document.querySelector('#reportsTable tbody');
                    let emptyRow = tbody.querySelector('.empty-state-row');

                    if (visibleCount === 0) {
                        if (!emptyRow) {
                            emptyRow = document.createElement('tr');
                            emptyRow.className = 'empty-state-row';
                            emptyRow.innerHTML = `
                    <td colspan="9" class="text-center text-muted py-4">
                        <i class="bx bx-search fs-1 text-muted"></i>
                        <p class="mb-0">No exceptions match your filter criteria</p>
                        <small>Try adjusting your filters</small>
                    </td>
                `;
                            tbody.appendChild(emptyRow);
                        }
                    } else if (emptyRow) {
                        emptyRow.remove();
                    }
                }

                // Initialize filters
                populateFiltersVanilla();

                // Add event listeners
                ['branchFilter', 'auditorFilter', 'statusFilter', 'riskRateFilter'].forEach(id => {
                    const element = document.getElementById(id);
                    if (element) {
                        element.addEventListener('change', filterTableVanilla);
                    }
                });

                // Reset functionality
                const resetBtn = document.getElementById('resetFilters');
                if (resetBtn) {
                    resetBtn.addEventListener('click', function() {
                        document.getElementById('branchFilter').value = '';
                        document.getElementById('auditorFilter').value = '';
                        document.getElementById('statusFilter').value = '';
                        document.getElementById('riskRateFilter').value = '';

                        // Show all rows
                        document.querySelectorAll('#reportsTable tbody tr').forEach(row => {
                            row.style.display = '';
                        });

                        // Remove empty state
                        const emptyRow = document.querySelector('.empty-state-row');
                        if (emptyRow) emptyRow.remove();
                    });
                }
            });
        </script>
    @endpush

</x-base-layout>
