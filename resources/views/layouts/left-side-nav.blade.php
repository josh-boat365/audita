<div class="vertical-menu">
    <div data-simplebar class="h-100">
        <!--- Sidemenu -->
        <div id="sidebar-menu">
            <!-- Left Menu Start -->
            <ul class="metismenu list-unstyled" id="side-menu">
                <li class="menu-title" key="t-menu">Menu</li>


                <li>
                    <a href="#" class="has-arrow waves-effect" aria-label="Dashboard Menu">
                        <i class="bx bx-home"></i>
                        <span key="t-dashboard">Dashboard</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        @if (in_array($employeeRoleId, $topManagers))
                            <li><a href="{{ route('dashboard') }}" key="t-default">Overview</a></li>
                        @else
                            <li><a href="{{ route('my.group.dashboard', $employeeId) }}" key="t-default">My
                                    Dashboard</a></li>
                        @endif
                    </ul>
                </li>

                @if (in_array($employeeRoleId, $topManagers) || in_array($employeeDepartmentId, $auditorDepartments))
                    <li>
                        <a href="#" class="has-arrow waves-effect" aria-label="Setup Menu">
                            <i class="bx bxs-cog"></i>
                            <span key="setup">Setup</span>
                        </a>
                        <ul class="sub-menu" aria-expanded="false">
                            <li><a href="{{ route('group') }}" key="default">Group Setup</a></li>
                            <li><a href="{{ route('members') }}" key="group-assignment">Group Members Assignment</a>
                            </li>
                            <li><a href="{{ route('unit') }}" key="unit-setup">Unit Setup</a></li>
                            <li><a href="{{ route('batch') }}" key="batch-setup">Batch Setup</a></li>
                            <li><a href="{{ route('process-type') }}" key="process-type-setup">Process Type Setup</a>
                            </li>
                            <li><a href="{{ route('risk-rate') }}" key="risk-rate-setup">Risk Rate Setup</a></li>
                        </ul>
                    </li>
                @endif

                <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect" aria-label="Reports Menu">
                        <i class="bx bx-detail"></i>
                        <span key="t-dashboards">Branch Exceptions</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('auditee.exception.list') }}"> Audit List</a></li>
                        <li><a href="{{ route('exception.list') }}" key="list">Internal Cont. List</a></li>
                        <li><a href="{{ route('auditee.pending.exception.list') }}">Pending</a></li>
                        <li><a href="{{ route('group.exception.enquiry.list') }}">Exception Enquiry</a></li>
                    </ul>
                </li>

                <li>
                    @if ($employeeDepartmentId === 8 || $employeeDepartmentId === 7)
                        <a href="#" class="has-arrow waves-effect" aria-label="Setup Menu">
                            <i class="bx bxs-file"></i>
                            <span key="setup">Exception Setup</span>
                        </a>
                        <ul class="sub-menu" aria-expanded="false">
                            @if ($employeeDepartmentId === 8)
                                {{--  IF USER IS IN THE INTERNAL CONTROL DEPARTMENT SHOW - DEPARTMENT ID - 8  --}}
                                <li><a href="{{ route('exception.create') }}" key="create">Internal Control
                                        Create</a>
                                </li>
                            @elseif($employeeDepartmentId === 7)
                                {{--  IF USER IS IN THE AUDIT CONTROL DEPARTMENT SHOW - DEPARTMENT ID - 7  --}}
                                <li><a href="{{ route('audit.create') }}" key="create">Audit Create</a></li>
                            @else
                                <div></div>
                            @endif

                            {{--  <li>
                            <a href="{{ route('exception.pending') }}" key="create">
                                Pending
                                @if ($pending_exception_count >= 0)
                                    <span class="badge rounded-full bg-danger">{{ $pending_exception_count }}</span>
                                @else
                                    <span></span>
                                @endif
                            </a>
                        </li>  --}}

                            <li><a href="" key=""></a></li>
                        </ul>
                    @else
                        <div></div>
                    @endif
                </li>

                @if (in_array($employeeRoleId, $topManagers) || $employeeDepartmentId === 7)
                    <li>
                        <a href="javascript: void(0);" class="has-arrow waves-effect" aria-label="Reports Menu">
                            <i class="bx bx-check-square"></i>
                            <span key="t-dashboards">Approvals</span>
                        </a>
                        <ul class="sub-menu" aria-expanded="false">
                            @if (in_array($employeeRoleId, $topManagers))
                                <li>
                                    <a href="{{ route('exception.supervisor.list') }}">Supervisor's Approval

                                    </a>
                                </li>
                            @else
                                <li><a href="{{ route('exception.auditor.list') }}">Auditor's Approval</a></li>
                            @endif
                        </ul>
                    </li>
                    <li>
                        <a href="javascript: void(0);" class="has-arrow waves-effect" aria-label="Reports Menu">
                            <i class="bx bx-analyse"></i>
                            <span key="t-dashboards">Exception Analysis</span>
                        </a>
                        <ul class="sub-menu" aria-expanded="false">
                            <li><a href="{{ route('auditor.analysis.exception') }}">Auditor's Analysis</a></li>
                        </ul>
                    </li>
                @else
                    <li></li>
                @endif

                @if (in_array($employeeRoleId, $topManagers) || in_array($employeeDepartmentId, $auditorDepartments))
                    <li>
                        <a href="javascript: void(0);" class="has-arrow waves-effect" aria-label="Reports Menu">
                            <i class="bx bx-file"></i>
                            <span key="t-dashboards">Reports</span>
                        </a>
                        <ul class="sub-menu" aria-expanded="false">
                            <li><a href="{{ route('reports') }}">Overview</a></li>
                        </ul>
                        <ul class="sub-menu" aria-expanded="false">
                            <li><a href="{{ route('auditor.report') }}">Auditor's Report</a></li>
                        </ul>
                    </li>
                @endif

            </ul>

        </div>
    </div>
    <!-- Sidebar -->
</div>
</div>
