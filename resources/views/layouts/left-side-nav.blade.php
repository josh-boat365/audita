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
                        @if ($user->empRole->id !== 1)
                            <li><a href="{{ route('dashboard.index') }}" key="t-default">Overview</a></li>
                            <li><a href="{{ route('show-batch') }}" key="t-default">My KPIs</a></li>
                        @endif


                        @if (isset($user) &&
                                (in_array($user->empRole->id, $managers) || in_array($user->id, $roleManagers) || $user->empRole->id == 1))
                            <li>
                                <a href="#" class="has-arrow waves-effect" aria-label="Supervisor Menu">
                                    <span key="t-setup">Supervisor</span>
                                </a>
                                <ul class="sub-menu" aria-expanded="false">
                                    <li><a href="{{ route('supervisor.index') }}" key="t-default">Employee KPIs</a></li>
                                </ul>
                            </li>
                        @else
                            <li></li>
                        @endif

                    </ul>
                </li>




                @if (isset($user) &&
                        $user->department->id == 10 &&
                        (in_array($user->id, $managers) || in_array($user->id, $roleManagers)))
                    <li>
                        <a href="#" class="has-arrow waves-effect" aria-label="HR Setup Menu">
                            <i class="bx bxs-cog"></i>
                            <span key="t-setup">HR Setup</span>
                        </a>
                        <ul class="sub-menu" aria-expanded="false">
                            <li><a href="{{ route('batch.setup.index') }}" key="t-default">Batch Setup</a></li>
                            <li><a href="{{ route('grade.index') }}" key="t -default">Grade Setup</a></li>
                            <li><a href="{{ route('global.index') }}" key="t-default">KPI Setup</a></li>
                            <li><a href="{{ route('global.weight.index') }}" key="t-default">Global Weighted
                                    Score</a></li>
                            <li><a href="{{ route('global.section.index') }}" key="t-default">Section Setup</a>
                            </li>
                            <li><a href="{{ route('global.metric.index') }}" key="t-default">Metric Setup</a>
                            </li>
                        </ul>
                    </li>
                    <li>
                        <a href="#" class="has-arrow waves-effect" aria-label="Department Setup Menu">
                            <i class="bx bxs-cog"></i>
                            <span key="t-setup">Department Setup</span>
                        </a>
                        <ul class="sub-menu" aria-expanded="false">
                            <li><a href="{{ route('kpi.index') }}" key="t-default">KPI Setup</a></li>
                        </ul>
                    </li>
                @elseif (isset($user) && $user->empRole->id !== 1 && (in_array($user->id, $managers) || in_array($user->id, $roleManagers)))
                    <li>
                        <a href="#" class="has-arrow waves-effect" aria-label="Department Setup Menu">
                            <i class="bx bxs-cog"></i>
                            <span key="t-setup">Department Setup</span>
                        </a>
                        <ul class="sub-menu" aria-expanded="false">
                            <li><a href="{{ route('kpi.index') }}" key="t-default">KPI Setup</a></li>
                        </ul>
                    </li>
                @else
                    <li></li>
                @endif


                @if ((isset($user) && $user->department->id == 10) || $user->empRole->id == 1)
                    <li>
                        <a href="javascript: void(0);" class="has-arrow waves-effect" aria-label="Reports Menu">
                            <i class="bx bx-file"></i>
                            <span key="t-dashboards">Reports</span>
                        </a>
                        <ul class="sub-menu" aria-expanded="false">
                            <li><a href="{{ route('report.index') }}">Overview</a></li>
                        </ul>
                    </li>
                @else
                    <li></li>
                @endif

                <hr style="margin: 25vh auto 1rem auto; width: 14rem;">

                {{--  Card for displaying support info  --}}
                <div class="card"
                    style="width: 14rem; height: fit-content; margin: 0 auto; background-color: #f2f5ff;">
                    <div class="card-body">
                        <h5 class="card-title">CONTACT SUPPORT</h5>
                        <p class="card-text">For any support,
                            please contact the IT department</p>
                        <p> <b>EMAIL:</b> <br> <a style="font-size: 9.22px; font-weight: bolder"
                                href="mailto:applicationsupport@bestpointgh.com">applicationsupport@bestpointgh.com</a>
                        </p>
                        <p><b>USER GUIDE:</b></p>
                        <div class="d-grid">
                            <a href="#" target="_blank"
                                class="btn btn-primary">Coming Soon</a>
                        </div>
                    </div>
                </div>
            </ul>

        </div>
    </div>
    <!-- Sidebar -->
</div>
</div>
