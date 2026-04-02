<x-base-layout>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <div class="container-fluid px-1">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Branch Exception Analysis & Reporting</h4>
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
                    <h4 class="card-title mb-4">
                        <i class="fas fa-filter me-2"></i>Branch & Exception Filters
                    </h4>
                    <form id="reportFilters">
                        <div class="row">
                            <!-- Primary Branch Filter -->
                            <div class="col-md-3">
                                <label for="branchFilter" class="form-label fw-bold text-primary">
                                    <i class="fas fa-map-marker-alt me-1"></i>Branch (Required)
                                </label>
                                <select id="branchFilter" class="form-select" required>
                                    <option value="">Select Branch...</option>
                                    @foreach ($groups->pluck('branchName')->unique() as $branchName)
                                        <option value="{{ $branchName }}">{{ $branchName }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Secondary Auditor Filter -->
                            <div class="col-md-3">
                                <label for="auditorFilter" class="form-label">
                                    <i class="fas fa-user me-1"></i>Auditor (Optional)
                                </label>
                                <select id="auditorFilter" class="form-select" disabled>
                                    <option value="">All Auditors in Branch</option>
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

                            <div class="col-md-2">
                                <label for="processTypeFilter" class="form-label">Process Type</label>
                                <select id="processTypeFilter" class="form-select">
                                    <option value="">All Process Types</option>
                                    @foreach (array_unique(array_column($reports, 'processType')) as $processType)
                                        @if ($processType)
                                            <option value="{{ $processType }}">{{ $processType }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-2">
                                <label for="departmentFilter" class="form-label">Department</label>
                                <select id="departmentFilter" class="form-select">
                                    <option value="">All Departments</option>
                                    @foreach (array_unique(array_column($reports, 'department')) as $department)
                                        @if ($department)
                                            <option value="{{ $department }}">{{ $department }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label for="batchFilter" class="form-label">Exception Batch</label>
                                <select id="batchFilter" class="form-select">
                                    <option value="">All Batches</option>
                                    @foreach (array_unique(array_column($reports, 'exceptionBatch')) as $batch)
                                        @if ($batch)
                                            <option value="{{ $batch }}">{{ $batch }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2">
                                <label for="dateFromFilter" class="form-label">From Date</label>
                                <input type="text" id="dateFromFilter" class="form-control datepicker"
                                    placeholder="Select start date">
                            </div>

                            <div class="col-md-2">
                                <label for="dateToFilter" class="form-label">To Date</label>
                                <input type="text" id="dateToFilter" class="form-control datepicker"
                                    placeholder="Select end date">
                            </div>

                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" id="resetFilters" class="btn btn-secondary me-2">
                                    <i class="fas fa-undo me-1"></i>Reset
                                </button>
                            </div>

                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" id="downloadReport" class="btn btn-success" disabled>
                                    <i class="fas fa-download me-1"></i>Download Report
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Preview Card -->
    <!-- Replace the existing preview content section with this simplified version -->
    <div class="card-body" style="min-height: 500px;">
        <!-- Loading State -->
        <div id="loadingState" class="text-center py-5" style="display: none;">
            <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Loading...</span>
            </div>
            <h5 class="mt-3 text-muted">Generating branch exception report...</h5>
            <p class="text-muted">Please wait while we process your filtered data.</p>
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="text-center py-5">
            <i class="fas fa-map-marker-alt fa-4x text-muted mb-3"></i>
            <h5 class="text-muted">Select a Branch to Begin</h5>
            <p class="text-muted">Choose a branch from the filter above to view exception analysis and generate
                reports.</p>
        </div>

        <!-- Preview Content - This will be populated by JavaScript -->
        <div id="previewContent" style="display: none;">
            <!-- Content will be dynamically generated here -->
        </div>
    </div>

    @push('scripts')
        <script src="https://unpkg.com/docx@8.5.0/build/index.js"></script>
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
            <script>
                $(document).ready(function () {
                    // ============================================
                    // GLOBAL VARIABLES
                    // ============================================
                    let filteredData = [];
                    let allReports = @json($reports);
                    let batches = @json($batches);
                    let groups = Array.isArray(@json($groups)) ? @json($groups) : Object.values(@json($groups));
                    let currentBranchAuditors = [];

                    // ============================================
                    // INITIALIZATION
                    // ============================================
                    function initialize() {
                        initializeDatePicker();
                        enhanceReportsWithBranchNames();
                        bindEventHandlers();
                        resetPreview();
                    }

                    function initializeDatePicker() {
                        flatpickr('.datepicker', {
                            dateFormat: 'Y-m-d'
                            , allowInput: true
                        });
                    }

                    function enhanceReportsWithBranchNames() {
                        allReports = allReports.map((report, idx) => {
                            const branchName = getBranchName(report.activityGroupId);
                            if (idx === 0) {
                                console.log('First report branch mapping:', {
                                    activityGroupId: report.activityGroupId,
                                    branchName: branchName,
                                    groupsCount: groups?.length,
                                    firstGroup: groups?.[0]
                                });
                            }
                            return {
                                ...report
                                , branchName: branchName
                            };
                        });
                    }

                    // ============================================
                    // UTILITY FUNCTIONS
                    // ============================================
                    function getBranchName(activityGroupId) {
                        if (!groups || groups.length === 0) {
                            console.warn('getBranchName: groups is empty or undefined');
                            return 'N/A';
                        }

                        for (let group of groups) {
                            if (group && group.id == activityGroupId) {
                                const branchName = group.branchName || group.name || 'N/A';
                                return branchName;
                            }
                        }
                        return 'N/A';
                    }

                    function showLoading() {
                        $('#loadingState').show();
                        $('#emptyState').hide();
                        $('#previewContent').hide();
                        $('#downloadReport').prop('disabled', true);
                    }

                    function hideLoading() {
                        $('#loadingState').hide();
                    }

                    function resetPreview() {
                        filteredData = [];
                        $('#emptyState').show();
                        $('#previewContent').hide();
                        $('#recordCount').text('0 records');
                        $('#downloadReport').prop('disabled', true);
                    }

                    // ============================================
                    // EVENT HANDLERS
                    // ============================================
                    function bindEventHandlers() {
                        $('#branchFilter').on('change', handleBranchSelection);
                        $('#auditorFilter, #statusFilter, #riskRateFilter, #departmentFilter, #processTypeFilter, #batchFilter, #dateFromFilter, #dateToFilter')
                            .on('change', handleFilterChange);
                        $('#resetFilters').on('click', handleResetFilters);
                        $('#dateFromFilter, #dateToFilter').on('change', handleDateValidation);
                        $('#downloadReport').on('click', handleDownloadReport);
                    }

                    function handleBranchSelection() {
                        const selectedBranch = $(this).val();
                        if (selectedBranch) {
                            populateAuditorFilter(selectedBranch);
                            applyFilters();
                        } else {
                            resetAuditorFilter();
                            resetPreview();
                        }
                    }

                    function handleFilterChange() {
                        if ($('#branchFilter').val()) {
                            applyFilters();
                        }
                    }

                    function handleResetFilters() {
                        $('#reportFilters')[0].reset();
                        resetAuditorFilter();
                        filteredData = [];
                        currentBranchAuditors = [];
                        resetPreview();
                    }

                    function handleDateValidation() {
                        const dateFrom = $('#dateFromFilter').val();
                        const dateTo = $('#dateToFilter').val();

                        if (dateFrom && dateTo && dateFrom > dateTo) {
                            alert('End date must be after start date');
                            $(this).val('');
                        }
                    }

                    //handle download report
                    function handleDownloadReport() {
                        downloadWordDocument();
                    }


                    // ============================================
                    // FILTER MANAGEMENT
                    // ============================================
                    function populateAuditorFilter(selectedBranch) {
                        const branchReports = allReports.filter(report => report.branchName === selectedBranch);
                        currentBranchAuditors = [...new Set(branchReports.map(r => r.auditorName))].filter(a => a);

                        $('#auditorFilter').prop('disabled', false).html(`
                                <option value="">All Auditors in ${selectedBranch}</option>
                                ${currentBranchAuditors.map(auditor => `<option value="${auditor}">${auditor}</option>`).join('')}
                            `);
                    }

                    function resetAuditorFilter() {
                        $('#auditorFilter').prop('disabled', true).html(
                            '<option value="">All Auditors in Branch</option>'
                        );
                    }

                    function getFilterValues() {
                        return {
                            branch: $('#branchFilter').val()
                            , auditor: $('#auditorFilter').val()
                            , status: $('#statusFilter').val()
                            , riskRate: $('#riskRateFilter').val()
                            , department: $('#departmentFilter').val()
                            , processType: $('#processTypeFilter').val()
                            , batch: $('#batchFilter').val()
                            , dateFrom: $('#dateFromFilter').val()
                            , dateTo: $('#dateToFilter').val()
                        };
                    }

                    function applyFilters() {
                        const filters = getFilterValues();

                        if (!filters.branch) {
                            resetPreview();
                            return;
                        }

                        showLoading();
                        setTimeout(() => {
                            console.log('Applying filters:', filters);
                            console.log('Total reports before filtering:', allReports.length);
                            console.log('Sample branch names:', allReports.slice(0, 3).map(r => r.branchName));

                            filteredData = filterReports(allReports, filters);

                            console.log('Total reports after filtering:', filteredData.length);
                            console.log('Filtered data:', filteredData.slice(0, 2));

                            displayBranchAnalysis(filteredData, filters);
                            hideLoading();
                        }, 1000);
                    }

                    function filterReports(reports, filters) {
                        return reports.filter(report => {
                            if (report.branchName !== filters.branch) return false;
                            if (filters.auditor && report.auditorName !== filters.auditor) return false;
                            if (filters.status && report.status !== filters.status) return false;
                            if (filters.riskRate && report.riskRate !== filters.riskRate) return false;
                            if (filters.department && report.department !== filters.department) return false;
                            if (filters.processType && report.processType !== filters.processType) return false;
                            if (filters.batch && report.exceptionBatch !== filters.batch) return false;

                            // Date filtering
                            if (filters.dateFrom || filters.dateTo) {
                                const occurrenceDate = report.occurrenceDate ? report.occurrenceDate.split('T')[0] :
                                    null;
                                if (filters.dateFrom && occurrenceDate && occurrenceDate < filters.dateFrom)
                                    return false;
                                if (filters.dateTo && occurrenceDate && occurrenceDate > filters.dateTo)
                                    return false;
                            }

                            return true;
                        });
                    }

                    // ============================================
                    // REPORT DISPLAY AND ANALYSIS
                    // ============================================
                    function displayBranchAnalysis(data, filters) {
                        try {
                            console.log('displayBranchAnalysis called with:', { dataLength: data.length, filters });

                            if (data.length === 0) {
                                console.warn('No data to display, showing empty state');
                                showEmptyState(filters.branch);
                                return;
                            }

                            $('#recordCount').text(`${data.length} exceptions`);

                            console.log('Generating report preview...');
                            generateReportPreview(data, filters);

                            console.log('Displaying filter summary...');
                            displayFilterSummary(filters);

                            $('#emptyState').hide();
                            $('#previewContent').show();
                            $('#downloadReport').prop('disabled', false);

                            console.log('Preview displayed successfully');
                        } catch (error) {
                            console.error('Error in displayBranchAnalysis:', error);
                            alert('Error displaying report: ' + error.message);
                        }
                    }

                    function showEmptyState(branchName) {
                        $('#emptyState').html(`
                                <i class="fas fa-search fa-4x text-muted mb-3"></i>
                                <h5 class="text-muted">No exceptions found for ${branchName}</h5>
                                <p class="text-muted">Try adjusting your filters or check if this branch has any recorded exceptions.</p>
                            `).show();
                        $('#previewContent').hide();
                        $('#downloadReport').prop('disabled', true);
                    }

                    function generateReportPreview(data, filters) {
                        try {
                            console.log('generateReportPreview: Generating title...');
                            const reportTitle = generateReportTitle(filters);

                            console.log('generateReportPreview: Generating summary...');
                            const executiveSummary = generateExecutiveSummary(data, filters);

                            console.log('generateReportPreview: Generating exception reports...');
                            const exceptionReports = generateExceptionReports(data);

                            console.log('generateReportPreview: Setting HTML content...');
                            $('#previewContent').html(`
                                        <!-- Report Title -->
                                        <div class="mb-4 text-center">
                                            <h3 class="text-primary font-weight-bold">${reportTitle}</h3>
                                            <hr class="my-4">
                                        </div>

                                        <!-- Executive Summary -->
                                        <div class="mb-5">
                                            <h4 class="text-primary border-bottom pb-2 mb-3">
                                                <i class="fas fa-chart-line me-2"></i>Executive Summary
                                            </h4>
                                            <div class="alert alert-light border-left-primary p-4">
                                                <div style="line-height: 1.8; text-align: justify;">${executiveSummary}</div>
                                            </div>
                                        </div>

                                        <!-- Exception Reports -->
                                        <div class="mb-4">
                                            <h4 class="text-primary border-bottom pb-2 mb-3">
                                                <i class="fas fa-list-alt me-2"></i>Exception Details
                                            </h4>
                                            <div class="exception-reports">
                                                ${exceptionReports}
                                            </div>
                                        </div>

                                        <!-- Applied Filters -->
                                        <div class="mb-4">
                                            <h5 class="text-muted border-bottom pb-2">
                                                <i class="fas fa-info-circle me-2"></i>Applied Filters
                                            </h5>
                                            <div id="filterSummary" class="alert alert-info mb-4"></div>
                                        </div>
                                    `);
                            console.log('generateReportPreview: Complete');
                        } catch (error) {
                            console.error('Error in generateReportPreview:', error);
                            console.error('Stack:', error.stack);
                            $('#previewContent').html(`
                                    <div class="alert alert-danger">
                                        <h5>Error generating preview</h5>
                                        <p>${error.message}</p>
                                        <small>Check console for details</small>
                                    </div>
                                `);
                        }
                    }

                    function generateReportTitle(filters) {
                        const auditPeriod = determineAuditPeriod(filters);
                        return `BEST POINT SAVINGS AND LOANS LIMITED INTERNAL AUDIT REPORT FOR ${filters.branch.toUpperCase()} BRANCH DURING THE ${auditPeriod} AUDIT`;
                    }

                    function determineAuditPeriod(filters) {
                        const currentYear = new Date().getFullYear();

                        if (filters.dateFrom && filters.dateTo) {
                            const fromYear = new Date(filters.dateFrom).getFullYear();
                            const toYear = new Date(filters.dateTo).getFullYear();
                            return fromYear === toYear ? fromYear.toString() : `${fromYear}-${toYear}`;
                        }

                        return currentYear.toString();
                    }

                    function generateExecutiveSummary(data, filters) {
                        const totalExceptions = data.length;
                        const uniqueAuditors = [...new Set(data.map(r => r.auditorName))].filter(a => a);
                        const uniqueDepartments = [...new Set(data.map(r => r.department))].filter(d => d);
                        const auditPeriod = determineAuditPeriod(filters);

                        // Analysis data
                        const riskDistribution = getRiskDistribution(data);
                        const statusAnalysis = getStatusAnalysis(data);

                        // Build summary
                        let summary =
                            `During the ${auditPeriod} internal audit exercise at <strong>${filters.branch}</strong> Branch, our audit team conducted a comprehensive review of operational processes, controls, and compliance measures. `;


                        summary +=
                            `This examination resulted in the identification of <strong>${totalExceptions}</strong> exception${totalExceptions !== 1 ? 's' : ''} across various operational areas that require management attention and corrective action. `;


                        if (uniqueDepartments.length > 0) {
                            summary +=
                                `The exceptions were identified across <strong>${uniqueDepartments.length}</strong> department${uniqueDepartments.length !== 1 ? 's' : ''}, `;

                            summary += uniqueDepartments.length > 3 ?
                                `indicating widespread areas requiring operational improvements. ` :
                                `indicating specific areas of operational focus: ${uniqueDepartments.join(', ')}. `;
                        }

                        // Risk assessment
                        const hasHighRisk = riskDistribution['High'] > 0;
                        const hasMediumRisk = riskDistribution['Medium'] > 0;
                        const hasLowRisk = riskDistribution['Low'] > 0;

                        if (hasHighRisk || hasMediumRisk || hasLowRisk) {
                            summary += `From a risk perspective, the audit findings include `;
                            const riskComponents = [];

                            if (hasHighRisk) riskComponents.push(
                                `${riskDistribution['High']} high-risk exception${riskDistribution['High'] !== 1 ? 's' : ''} requiring immediate corrective action`
                            );
                            if (hasMediumRisk) riskComponents.push(
                                `${riskDistribution['Medium']} medium-risk exception${riskDistribution['Medium'] !== 1 ? 's' : ''} needing prompt attention`
                            );
                            if (hasLowRisk) riskComponents.push(
                                `${riskDistribution['Low']} low-risk exception${riskDistribution['Low'] !== 1 ? 's' : ''} for ongoing monitoring`
                            );

                            summary += riskComponents.join(', ') + '. ';
                        }

                        // Resolution status
                        const {
                            resolvedCount
                            , pendingCount
                            , resolutionRate
                        } = statusAnalysis;
                        summary +=
                            `Management has demonstrated commitment to addressing audit findings with <strong>${resolvedCount}</strong> exception${resolvedCount !== 1 ? 's' : ''} already resolved`;


                        if (pendingCount > 0) {
                            summary +=
                                `, while <strong>${pendingCount}</strong> exception${pendingCount !== 1 ? 's remain' : ' remains'} pending resolution`;

                        }

                        summary += `, achieving a <strong>${resolutionRate}%</strong> resolution rate. `;


                        // Operational assessment
                        const rate = parseFloat(resolutionRate);
                        if (rate >= 80) {
                            summary +=
                                `This demonstrates strong management commitment and effective control environment at the branch, with robust exception management processes in place.`;
                        } else if (rate >= 60) {
                            summary +=
                                `While progress has been made in addressing audit findings, there remains opportunity for improvement in exception resolution timelines and control effectiveness.`;
                        } else {
                            summary +=
                                `The current resolution rate indicates the need for enhanced management focus on audit findings, improved control processes, and more timely corrective actions to strengthen the branch's operational environment.`;
                        }

                        return summary;
                    }

                    function generateExceptionReports(data) {
                        if (data.length === 0)
                            return '<p class="text-muted">No exceptions found for the selected criteria.</p>';

                        let reportHtml = '';
                        const groupedExceptions = groupExceptionsByTitle(data);

                        Object.keys(groupedExceptions).forEach(title => {
                            const exceptions = groupedExceptions[title];
                            reportHtml += generateSingleExceptionReport(title, exceptions);
                        });

                        return reportHtml;
                    }

                    function groupExceptionsByTitle(data) {
                        const groups = {};

                        data.forEach(exception => {
                            let groupTitle = exception.exceptionTitle || 'UNTITLED EXCEPTION';
                            groupTitle = groupTitle.toUpperCase().trim();

                            // If no title but has exception description, create title from first part
                            if (groupTitle === 'UNTITLED EXCEPTION' && exception.exception) {
                                const firstSentence = exception.exception.split('.')[0].trim();
                                if (firstSentence.length > 0) {
                                    groupTitle = firstSentence.toUpperCase().substring(0, 80);
                                    if (exception.exception.length > 80) groupTitle += '...';
                                }
                            }

                            if (!groups[groupTitle]) {
                                groups[groupTitle] = [];
                            }
                            groups[groupTitle].push(exception);
                        });

                        return groups;
                    }

                    // Updated generateSingleExceptionReport function with new structure
                    function generateSingleExceptionReport(title, exceptions) {
                        const primaryException = exceptions[0];
                        const multipleInstances = exceptions.length > 1;

                        let reportHtml = `
                        <div class="exception-report mb-5 p-4" style="border: 1px solid #e9ecef; border-radius: 8px; background: #fafafa;">
                            <!-- Exception Title -->
                            <h5 class="exception-title text-primary mb-3" style="font-weight: bold;">
                                **${title}**${multipleInstances ? ` <span class="badge bg-info ms-2">${exceptions.length} instances</span>` : ''}
                            </h5>

                            <!-- Exception Description -->
                            <div class="exception-description mb-3" style="line-height: 1.8;">
                                <strong>Exception Description:</strong><br>
                                <div style="margin-top: 8px;">
                                    ${generateExceptionDescription(primaryException, exceptions)}
                                </div>
                            </div>`;

                        // Root Cause (if available)
                        if (primaryException.rootCause) {
                            reportHtml += `
                            <!-- Root Cause -->
                            <div class="root-cause mb-3" style="background: #fff3cd; padding: 15px; border-radius: 6px; border-left: 4px solid #ffc107;">
                                <strong style="color: #856404;">Root Cause:</strong><br>
                                <div style="line-height: 1.8; margin-top: 8px;">
                                    ${primaryException.rootCause}
                                </div>
                            </div>`;
                        }

                        reportHtml += `
                            <!-- Analysis on Exception -->
                            <div class="exception-analysis mb-3" style="background: #f8f9fa; padding: 15px; border-radius: 6px; border-left: 4px solid #6c757d;">
                                <strong style="color: #495057;">Analysis:</strong><br>
                                <div style="line-height: 1.8; margin-top: 8px;">
                                    ${generateExceptionAnalysis(primaryException, exceptions)}
                                </div>
                            </div>`;

                        // Risk Analysis (new section)
                        if (primaryException.riskAnalysis) {
                            reportHtml += `
                            <!-- Risk Analysis -->
                            <div class="risk-analysis mb-3" style="background: #fff3cd; padding: 15px; border-radius: 6px; border-left: 4px solid #ffc107;">
                                <strong style="color: #856404;">Risk Analysis:</strong><br>
                                <div style="line-height: 1.8; margin-top: 8px;">
                                    ${primaryException.riskAnalysis}
                                </div>
                            </div>`;
                        }

                        // Recommendation (new section)
                        if (primaryException.recommendation) {
                            reportHtml += `
                            <!-- Audit Recommendation -->
                            <div class="audit-recommendation mb-3" style="background: #d4edda; padding: 15px; border-radius: 6px; border-left: 4px solid #28a745;">
                                <strong style="color: #155724;">Audit Recommendation:</strong><br>
                                <div style="margin-top: 8px; line-height: 1.6;">
                                    ${generateRecommendationContent(exceptions)}
                                </div>
                            </div>`;
                        }

                        reportHtml += `
                            <!-- Management Response -->
                            <div class="management-response mb-3" style="background: #e8f4f8; padding: 15px; border-radius: 6px; border-left: 4px solid #17a2b8;">
                                <strong style="color: #0c5460;">Management Response${multipleInstances ? 's' : ''}</strong><br>
                                <div style="margin-top: 8px; line-height: 1.6;">
                                    ${generateManagementResponse(exceptions)}
                                </div>
                            </div>

                        </div>`;

                        return reportHtml;
                    }

                    // NEW: Function to handle recommendation content for multiple instances
                    function generateRecommendationContent(exceptions) {
                        const recommendations = {};

                        exceptions.forEach(exception => {
                            const recommendation = exception.recommendation || 'No specific recommendation provided';
                            if (!recommendations[recommendation]) {
                                recommendations[recommendation] = 0;
                            }
                            recommendations[recommendation]++;
                        });

                        const recommendationEntries = Object.entries(recommendations);

                        if (recommendationEntries.length === 1) {
                            return recommendationEntries[0][0];
                        } else {
                            let content = '';
                            recommendationEntries.forEach(([rec, count], index) => {
                                if (index > 0) content += '<br><br>';
                                if (count > 1) {
                                    content += `<strong>Recommendation ${index + 1}</strong> <strong>(${count} instance${count !== 1 ? 's' : ''})</strong>:<br>`;

                                }
                                content += rec;
                            });
                            return content;
                        }
                    }



                    function generateExceptionDescription(primaryException, allExceptions) {
                        let description = primaryException.exception ||
                            'No detailed description provided for this exception.';

                        if (allExceptions.length > 1) {
                            const departments = [...new Set(allExceptions.map(e => e.department))].filter(d => d);
                            const processTypes = [...new Set(allExceptions.map(e => e.processType))].filter(p => p);

                            description +=
                                `<br><br><strong>Scope:</strong> This exception was identified in <strong>${allExceptions.length}</strong> instances`;


                            if (departments.length > 1) {
                                description += ` across multiple departments (${departments.join(', ')})`;
                            } else if (departments.length === 1) {
                                description += ` within the <strong>${departments[0]}</strong> department`;

                            }

                            if (processTypes.length > 1) {
                                description += ` affecting ${processTypes.length} different process types`;
                            } else if (processTypes.length === 1) {
                                description += ` related to ${processTypes[0]} processes`;
                            }

                            description += '.';
                        }

                        return description;
                    }

                    // Updated generateExceptionAnalysis function (simplified since riskAnalysis is now separate)
                    function generateExceptionAnalysis(primaryException, allExceptions) {
                        let analysis = '';

                        // Impact analysis based on departments and process types
                        const departments = [...new Set(allExceptions.map(e => e.department))].filter(d => d);
                        const processTypes = [...new Set(allExceptions.map(e => e.processType))].filter(p => p);

                        if (departments.includes('Operations') || processTypes.includes('Customer Service')) {
                            analysis += `The operational nature of this exception could impact customer service delivery and branch efficiency. `;
                        }

                        if (departments.includes('Credit') || processTypes.includes('Loan Processing')) {
                            analysis += `This exception in credit operations could affect loan portfolio quality and regulatory compliance. `;
                        }

                        if (departments.includes('Treasury') || processTypes.includes('Cash Management')) {
                            analysis += `The treasury-related nature of this exception poses risks to cash management and financial controls. `;
                        }

                        // Control environment assessment
                        if (allExceptions.length > 1) {
                            analysis += `The recurrence of this exception across <strong>${allExceptions.length}</strong> instances indicates potential systemic control weaknesses that require comprehensive review. `;

                        }

                        // Resolution urgency
                        const unresolvedCount = allExceptions.filter(e => e.status !== 'RESOLVED').length;
                        if (unresolvedCount > 0) {
                            analysis += `With <strong>${unresolvedCount} instance${unresolvedCount !== 1 ? 's' : ''}</strong> still pending resolution, immediate action is required to mitigate ongoing operational risks.`;

                        } else {
                            analysis += `All instances of this exception have been resolved, demonstrating effective management response to audit findings.`;
                        }

                        // Fallback if no specific analysis is generated
                        if (!analysis.trim()) {
                            analysis = `This exception requires management review and appropriate corrective measures to ensure operational compliance and effectiveness. The identified control gaps need to be addressed to prevent recurrence and maintain operational integrity.`;
                        }

                        return analysis;
                    }

                    // Function to generate recommendation content for multiple instances
                    function generateRecommendationContent(exceptions) {
                        const recommendations = {};

                        exceptions.forEach(exception => {
                            const recommendation = exception.recommendation || 'Management should implement appropriate controls to prevent recurrence of this exception.';
                            if (!recommendations[recommendation]) {
                                recommendations[recommendation] = 0;
                            }
                            recommendations[recommendation]++;
                        });

                        const recommendationEntries = Object.entries(recommendations);

                        if (recommendationEntries.length === 1) {
                            return recommendationEntries[0][0];
                        } else {
                            let content = '';
                            recommendationEntries.forEach(([rec, count], index) => {
                                if (index > 0) content += '<br><br>';
                                if (count > 1) {
                                    content += `<strong>Recommendation ${index + 1}</strong> (${count} instance${count !== 1 ? 's' : ''}):<br>`;
                                }
                                content += rec;
                            });
                            return content;
                        }
                    }




                    function generateManagementResponse(exceptions) {
                        let response = '';

                        // Group responses by status comment
                        const responseGroups = {};
                        exceptions.forEach(exception => {
                            const statusComment = exception.statusComment || 'No management response provided';
                            const status = exception.status || 'PENDING';
                            const key = `${statusComment}_${status}`;

                            if (!responseGroups[key]) {
                                responseGroups[key] = {
                                    comment: statusComment
                                    , status: status
                                    , count: 0
                                };
                            }
                            responseGroups[key].count++;
                        });

                        const responses = Object.values(responseGroups);

                        if (responses.length === 1) {
                            const resp = responses[0];
                            response = `<strong>Status:</strong> ${resp.status}<br>`;
                            response += `<strong>Response:</strong> ${resp.comment}`;
                        } else {
                            responses.forEach((resp, index) => {
                                response +=
                                    `<strong>Response ${index + 1}</strong> (${resp.count} instance${resp.count !== 1 ? 's' : ''}):<br>`;
                                response += `<strong>Status:</strong> ${resp.status}<br>`;
                                response += `<strong>Comment:</strong> ${resp.comment}`;
                                if (index < responses.length - 1) response += '<br><br>';
                            });
                        }

                        return response;
                    }

                    // ============================================
                    // ANALYSIS HELPER FUNCTIONS
                    // ============================================
                    function getRiskDistribution(data) {
                        const distribution = {};
                        data.forEach(report => {
                            if (report.riskRate) {
                                distribution[report.riskRate] = (distribution[report.riskRate] || 0) + 1;
                            }
                        });
                        return distribution;
                    }

                    function getStatusAnalysis(data) {
                        const resolvedCount = data.filter(r => r.status === 'RESOLVED').length;
                        const approvedCount = data.filter(r => r.status === 'APPROVED').length;
                        const pendingCount = data.filter(r => r.status === 'PENDING' || r.status === 'OPEN').length;
                        const resolutionRate = data.length > 0 ? ((resolvedCount / data.length) * 100).toFixed(1) : 0;

                        return {
                            resolvedCount
                            , approvedCount
                            , pendingCount
                            , resolutionRate
                        };
                    }

                    function displayFilterSummary(filters) {
                        const activeFilters = [];
                        Object.keys(filters).forEach(key => {
                            if (filters[key]) {
                                const label = key.charAt(0).toUpperCase() + key.slice(1).replace(/([A-Z])/g, ' $1');
                                activeFilters.push(`<strong>${label}:</strong> ${filters[key]}`);
                            }
                        });

                        $('#filterSummary').html(
                            activeFilters.length > 1 ?
                                activeFilters.join(' | ') :
                                `<strong>Branch:</strong> ${filters.branch} (All other parameters included)`
                        );
                    }

                        // ============================================
                        // EXPORT FUNCTIONALITY
                        // ============================================
                        /*function generateAndSubmitExportForm() {
                            const filters = getFilterValues();
                            const analysisData = {
                                executiveSummary: $('#summaryContent').html()
                                , qualitativeAnalysis: $('#analysisContent').html()
                                , keyMetrics: generateMetricsForExport(filteredData)
                                , filterSummary: $('#filterSummary').text()
                            };

                            const form = $('<form>', {
                                'method': 'POST'
                                {{--  , 'action': '{{ route('
                                reports.export-word ') }}'  --}}
                                , 'target': '_blank'
                            });

                            // Add form fields
                            form.append($('<input>', {
                                'type': 'hidden'
                                , 'name': '_token'
                                , 'value': '{{ csrf_token() }}'
                }));
                form.append($('<input>', {
                    'type': 'hidden'
                    , 'name': 'filters'
                    , 'value': JSON.stringify(filters)
                }));
                form.append($('<input>', {
                    'type': 'hidden'
                    , 'name': 'data'
                    , 'value': JSON.stringify(filteredData)
                }));
                form.append($('<input>', {
                    'type': 'hidden'
                    , 'name': 'analysis'
                    , 'value': JSON.stringify(analysisData)
                }));

                form.appendTo('body').submit().remove();
                        }*/

                function generateMetricsForExport(data) {
                    return {
                        totalExceptions: data.length
                        , riskDistribution: getRiskDistribution(data)
                        , statusDistribution: getStatusDistribution(data)
                        , departmentSpread: [...new Set(data.map(r => r.department))].length
                        , processTypeSpread: [...new Set(data.map(r => r.processType))].length
                        , uniqueAuditors: [...new Set(data.map(r => r.auditorName))].filter(a => a).length
                    };
                }

                function getStatusDistribution(data) {
                    const distribution = {};
                    data.forEach(report => {
                        if (report.status) {
                            distribution[report.status] = (distribution[report.status] || 0) + 1;
                        }
                    });
                    return distribution;
                }


                // ============================================
                // WORD DOCUMENT DOWNLOAD FUNCTION
                // ============================================

                function downloadWordDocument() {
                    if (filteredData.length === 0) {
                        alert('No data to export. Please select a branch and apply filters first.');
                        return;
                    }

                    showLoading();

                    try {
                        // Generate the Word document content
                        const wordContent = generateWordDocumentContent();

                        // Create and download the file
                        createAndDownloadWordFile(wordContent);

                    } catch (error) {
                        console.error('Error generating Word document:', error);
                        alert('Error generating document. Please try again.');
                    } finally {
                        hideLoading();
                    }
                }

                function generateWordDocumentContent() {
                    const filters = getFilterValues();
                    const reportTitle = generateReportTitle(filters);
                    const executiveSummary = generateExecutiveSummary(filteredData, filters);
                    const exceptionReports = generateExceptionReportsForWord(filteredData);
                    const filterSummary = generateFilterSummaryForWord(filters);

                    // Generate Word-compatible HTML content - to word
                    const wordContent = `
                                    <!DOCTYPE html>
                                    <html>
                                    <head>
                                        <meta charset="utf-8">
                                        <title>${reportTitle}</title>
                                        <style>
                                            body {
                                                font-family: 'Arial', serif;
                                                font-size: 12pt;
                                                line-height: 1.6;
                                                margin: 1in;
                                                color: #000;
                                            }
                                            .report-title {
                                                text-align: center;
                                                font-weight: bold;
                                                font-size: 14pt;
                                                margin-bottom: 30px;
                                                text-transform: uppercase;
                                                line-height: 1.4;
                                            }
                                            .section-header {
                                                font-weight: bold;
                                                font-size: 13pt;
                                                margin-top: 25px;
                                                margin-bottom: 15px;
                                                border-bottom: 1px solid #000;
                                                padding-bottom: 5px;
                                            }
                                            .executive-summary {
                                                text-align: justify;
                                                margin-bottom: 25px;
                                                padding: 15px;
                                                border: 1px solid #000;
                                            }
                                            .exception-item {
                                                margin-bottom: 20px;
                                                page-break-inside: avoid;
                                                border: 1px solid #ccc;
                                                padding: 15px;
                                            }
                                            .exception-title {
                                                font-weight: bold;
                                                margin-bottom: 10px;
                                                text-decoration: underline;
                                            }
                                            .exception-description {
                                                text-align: justify;
                                                margin-bottom: 10px;
                                            }
                                            .exception-analysis {
                                                text-align: justify;
                                                margin-bottom: 10px;
                                                font-style: italic;
                                            }
                                            .management-response {
                                                background-color: #f5f5f5;
                                                padding: 10px;
                                                border-left: 3px solid #000;
                                                margin-top: 10px;
                                            }
                                            .filter-summary {
                                                font-size: 10pt;
                                                margin-top: 20px;
                                                padding: 10px;
                                                border: 1px solid #000;
                                            }
                                            .page-break {
                                                page-break-before: always;
                                            }
                                            table {
                                                width: 100%;
                                                border-collapse: collapse;
                                                margin: 10px 0;
                                            }
                                            th, td {
                                                border: 1px solid #000;
                                                padding: 8px;
                                                text-align: left;
                                            }
                                            th {
                                                background-color: #f0f0f0;
                                                font-weight: bold;
                                            }
                                        </style>
                                    </head>
                                    <body>
                                        <!-- Report Title -->
                                        <div class="report-title">
                                            ${reportTitle}
                                        </div>

                                        <!-- Executive Summary -->
                                        <div class="section-header">EXECUTIVE SUMMARY</div>
                                        <div class="executive-summary">
                                            ${cleanHtmlForWord(executiveSummary)}
                                        </div>

                                        <!-- Exception Details -->
                                        <div class="section-header">EXCEPTION DETAILS</div>
                                        ${exceptionReports}

                                        <!-- Applied Filters -->
                                        <div class="section-header">APPLIED FILTERS</div>
                                        <div class="filter-summary">
                                            ${filterSummary}
                                        </div>

                                        <!-- Report Footer -->
                                        <div style="margin-top: 40px; text-align: center; font-size: 10pt;">
                                            <hr>
                                            <p>Generated on: ${new Date().toLocaleDateString()} at ${new Date().toLocaleTimeString()}</p>
                                            <p>Total Exceptions: ${filteredData.length}</p>
                                        </div>
                                    </body>
                                    </html>`;

                    return wordContent;
                }

                // Updated generateExceptionReportsForWord function for Word export
                function generateExceptionReportsForWord(data) {
                    if (data.length === 0) {
                        return '<p>No exceptions found for the selected criteria.</p>';
                    }

                    let reportHtml = '';
                    const groupedExceptions = groupExceptionsByTitle(data);
                    let exceptionNumber = 1;

                    Object.keys(groupedExceptions).forEach(title => {
                        const exceptions = groupedExceptions[title];
                        const primaryException = exceptions[0];
                        const multipleInstances = exceptions.length > 1;

                        reportHtml += `
                        <div class="exception-item">
                            <div class="exception-title">
                                ${exceptionNumber}. ${cleanHtmlForWord(title)}${multipleInstances ? ` (${exceptions.length} instances)` : ''}
                            </div>

                            <div class="exception-description">
                                <strong>Exception Description:</strong><br>
                                ${cleanHtmlForWord(generateExceptionDescription(primaryException, exceptions))}
                            </div>`;

                        // Root Cause (if available)
                        if (primaryException.rootCause) {
                            reportHtml += `
                            <div style="margin: 10px 0; padding: 10px; background: #fff3cd; border-left: 3px solid #ffc107;">
                                <strong>Root Cause:</strong><br>
                                ${cleanHtmlForWord(primaryException.rootCause)}
                            </div>`;
                        }

                        // Analysis
                        reportHtml += `
                            <div class="exception-analysis">
                                <strong>Analysis:</strong><br>
                                ${cleanHtmlForWord(generateExceptionAnalysis(primaryException, exceptions))}
                            </div>`;

                        // Risk Analysis (new section)
                        if (primaryException.riskAnalysis) {
                            reportHtml += `
                            <div style="margin: 10px 0; padding: 10px; background: #fff3cd; border-left: 3px solid #ffc107;">
                                <strong>Risk Analysis:</strong><br>
                                ${cleanHtmlForWord(primaryException.riskAnalysis)}
                            </div>`;
                        }

                        // Recommendation (new section)
                        if (primaryException.recommendation) {
                            reportHtml += `
                            <div style="margin: 10px 0; padding: 10px; background: #d4edda; border-left: 3px solid #28a745;">
                                <strong>Audit Recommendation:</strong><br>
                                ${cleanHtmlForWord(generateRecommendationContent(exceptions))}
                            </div>`;
                        }

                        // Management Response
                        reportHtml += `
                            <div class="management-response">
                                <strong>Management Response${multipleInstances ? 's' : ''}:</strong><br>
                                ${cleanHtmlForWord(generateManagementResponse(exceptions))}
                            </div>`;

                        reportHtml += `
                        </div>`;
                        exceptionNumber++;
                    });

                    return reportHtml;
                }




                function generateFilterSummaryForWord(filters) {
                    const activeFilters = [];

                    Object.keys(filters).forEach(key => {
                        if (filters[key]) {
                            const label = key.charAt(0).toUpperCase() + key.slice(1).replace(/([A-Z])/g, ' $1');
                            activeFilters.push(`${label}: ${filters[key]}`);
                        }
                    });

                    return activeFilters.join(' | ') || `Branch: ${filters.branch} (All other parameters included)`;
                }

                function cleanHtmlForWord(htmlString) {
                    if (!htmlString) return '';

                    return htmlString
                        .replace(/<br\s*\/?>/gi, '\n')
                        .replace(/<\/p>/gi, '\n\n')
                        .replace(/<p[^>]*>/gi, '')
                        .replace(/<strong>/gi, '<b>')
                        .replace(/<\/strong>/gi, '</b>')
                        .replace(/<em>/gi, '<i>')
                        .replace(/<\/em>/gi, '</i>')
                        .replace(/<[^>]*>/g, '') // Remove any remaining HTML tags
                        .replace(/\n\n\n+/g, '\n\n') // Clean up excessive line breaks
                        .trim();
                }

                function createAndDownloadWordFile(content) {
                    // Create blob with proper MIME type for Word document
                    const blob = new Blob([content], {
                        type: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
                    });

                    // Generate filename
                    const filters = getFilterValues();
                    const timestamp = new Date().toISOString().slice(0, 10);
                    const filename = `Exception_Report_${filters.branch.replace(/\s+/g, '_')}_${timestamp}.doc`;

                    // Create download link and trigger download
                    const link = document.createElement('a');
                    link.href = URL.createObjectURL(blob);
                    link.download = filename;
                    link.style.display = 'none';

                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);

                    // Clean up the blob URL
                    setTimeout(() => {
                        URL.revokeObjectURL(link.href);
                    }, 100);
                }


                // ============================================
                // INITIALIZE APPLICATION
                // ============================================
                initialize();
                    });

            </script>

            <style>
                /* Updated CSS for structured exception reports with new sections */
                .exception-report {
                    margin-bottom: 2rem;
                    padding: 1.5rem;
                    border: 1px solid #e9ecef;
                    border-radius: 8px;
                    background: #fafafa;
                    page-break-inside: avoid;
                }

                .exception-title {
                    font-weight: bold;
                    color: #007bff;
                    margin-bottom: 1rem;
                    font-size: 1.1rem;
                    text-transform: uppercase;
                    letter-spacing: 0.5px;
                }

                .exception-description {
                    line-height: 1.8;
                    margin-bottom: 1rem;
                    text-align: justify;
                    color: #333;
                    padding: 0.75rem;
                    background: #ffffff;
                    border-left: 4px solid #007bff;
                    border-radius: 4px;
                }

                .exception-description strong {
                    color: #007bff;
                    text-decoration: underline;
                }

                .root-cause {
                    margin-bottom: 1rem;
                    padding: 0.75rem;
                    background: #fff3cd;
                    border-left: 4px solid #ffc107;
                    border-radius: 4px;
                }

                .root-cause strong {
                    color: #856404;
                    text-decoration: underline;
                }

                .exception-analysis {
                    line-height: 1.8;
                    margin-bottom: 1rem;
                    text-align: justify;
                    color: #333;
                    padding: 0.75rem;
                    background: #f8f9fa;
                    border-left: 4px solid #6c757d;
                    border-radius: 4px;
                }

                .exception-analysis strong {
                    color: #495057;
                    text-decoration: underline;
                }

                /* Risk Analysis styling */
                .risk-analysis {
                    background: #fff3cd;
                    padding: 1rem;
                    border-radius: 6px;
                    border-left: 4px solid #ffc107;
                    margin-bottom: 1rem;
                    line-height: 1.6;
                }

                .risk-analysis strong {
                    color: #856404;
                    text-decoration: underline;
                }

                /* Audit Recommendation styling */
                .audit-recommendation {
                    background: #d4edda;
                    padding: 1rem;
                    border-radius: 6px;
                    border-left: 4px solid #28a745;
                    margin-bottom: 1rem;
                    line-height: 1.6;
                }

                .audit-recommendation strong {
                    color: #155724;
                    text-decoration: underline;
                }

                .management-response {
                    background: #e8f4f8;
                    padding: 1rem;
                    border-radius: 6px;
                    border-left: 4px solid #17a2b8;
                    line-height: 1.6;
                }

                .management-response strong {
                    color: #0c5460;
                    text-decoration: underline;
                }

                /* Report title styling */
                .report-title {
                    font-size: 1.4rem;
                    font-weight: bold;
                    text-align: center;
                    margin-bottom: 2rem;
                    padding: 1.5rem;
                    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
                    color: white;
                    border-radius: 8px;
                    line-height: 1.4;
                }

                /* Executive summary styling */
                .executive-summary {
                    background: #f8f9fa;
                    border-left: 4px solid #007bff;
                    padding: 1.5rem;
                    margin-bottom: 2rem;
                    border-radius: 6px;
                    line-height: 1.8;
                    text-align: justify;
                }

                /* Badge styling for multiple instances */
                .badge {
                    font-size: 0.75rem;
                    padding: 0.25rem 0.5rem;
                    border-radius: 12px;
                }

                .bg-info {
                    background-color: #17a2b8 !important;
                    color: white;
                }

                /* Section headers */
                .section-header {
                    color: #007bff;
                    border-bottom: 2px solid #e9ecef;
                    padding-bottom: 0.5rem;
                    margin-bottom: 1.5rem;
                    font-weight: 600;
                }

                /* Filter summary styling */
                .filter-summary {
                    background: #e3f2fd;
                    border: 1px solid #bbdefb;
                    border-radius: 6px;
                    padding: 1rem;
                    font-size: 0.95rem;
                    line-height: 1.5;
                }

                /* Print-specific styles */
                @media print {
                    .exception-report {
                        box-shadow: none !important;
                        border: 1px solid #ddd !important;
                        margin-bottom: 1.5rem;
                        page-break-inside: avoid;
                    }

                    .management-response,
                    .exception-analysis,
                    .root-cause,
                    .risk-analysis,
                    .audit-recommendation,
                    .exception-description {
                        background: white !important;
                        border-left: 2px solid #333 !important;
                        color: black !important;
                    }

                    .exception-title {
                        color: black !important;
                        font-weight: bold;
                    }

                    .report-title {
                        background: white !important;
                        color: black !important;
                        border: 2px solid #333 !important;
                    }

                    .btn,
                    .form-control,
                    .form-select {
                        display: none !important;
                    }

                    .badge {
                        border: 1px solid #333 !important;
                        background: white !important;
                        color: black !important;
                    }

                    /* Ensure all section headings are bold and visible in print */
                    .risk-analysis strong,
                    .audit-recommendation strong,
                    .management-response strong,
                    .exception-analysis strong,
                    .root-cause strong,
                    .exception-description strong {
                        color: black !important;
                        font-weight: bold;
                    }
                }

                /* Responsive design */
                @media (max-width: 768px) {
                    .exception-report {
                        padding: 1rem;
                        margin-bottom: 1.5rem;
                    }

                    .report-title {
                        font-size: 1.1rem;
                        padding: 1rem;
                        line-height: 1.3;
                    }

                    .exception-title {
                        font-size: 1rem;
                    }

                    .exception-description,
                    .exception-analysis,
                    .risk-analysis,
                    .audit-recommendation {
                        font-size: 0.95rem;
                        line-height: 1.6;
                    }

                    .management-response {
                        padding: 0.75rem;
                        font-size: 0.9rem;
                    }
                }

                /* Text formatting helpers */
                .text-justify {
                    text-align: justify;
                }

                .line-height-relaxed {
                    line-height: 1.8;
                }

                /* Status-specific styling */
                .status-resolved {
                    background: #d4edda;
                    border-left-color: #28a745;
                }

                .status-pending {
                    background: #fff3cd;
                    border-left-color: #ffc107;
                }

                .status-approved {
                    background: #e8f4f8;
                    border-left-color: #17a2b8;
                }

                /* Risk-specific styling */
                .risk-high {
                    border-left-color: #dc3545 !important;
                    background: #f8d7da;
                }

                .risk-medium {
                    border-left-color: #ffc107 !important;
                    background: #fff3cd;
                }

                .risk-low {
                    border-left-color: #28a745 !important;
                    background: #d4edda;
                }
            </style>
    @endpush
</x-base-layout>
