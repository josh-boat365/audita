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

                        <li><a href="{{ route('dashboard') }}" key="t-default">Overview</a></li>
                        {{--  <li><a href="" key="t-default"></a></li>  --}}


                        {{--  <li>
                            <a href="#" class="has-arrow waves-effect" aria-label="Supervisor Menu">
                                <span key="t-setup">Supervisor</span>
                            </a>
                            <ul class="sub-menu" aria-expanded="false">
                                <li><a href="" key="t-default">Employee KPIs</a></li>
                            </ul>
                        </li>  --}}

                    </ul>
                </li>


                <li>
                    <a href="#" class="has-arrow waves-effect" aria-label="Setup Menu">
                        <i class="bx bxs-cog"></i>
                        <span key="setup">Setup</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('group') }}" key="default">Group Setup</a></li>
                        <li><a href="{{ route('members') }}" key="">Group Members Assignment</a></li>
                        <li><a href="{{ route('unit') }}" key="">Unit Setup</a></li>
                        <li><a href="{{ route('batch') }}" key="">Batch Setup</a></li>
                        <li><a href="{{ route('process-type') }}" key="">Process Type Setup</a></li>
                        <li><a href="{{ route('risk-rate') }}" key="">Risk Rate Setup</a></li>
                        {{--  <li><a href="" key=""></a></li>  --}}
                    </ul>
                </li>

                <li>
                    <a href="#" class="has-arrow waves-effect" aria-label="Setup Menu">
                        <i class="bx bxs-file"></i>
                        <span key="setup">Exception Setup</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="{{ route('exception.list') }}" key="default">List</a></li>
                        <li><a href="{{ route('exception.create') }}" key="default">Create</a></li>

                        {{--  <li><a href="" key="">Batch Assignment</a></li>  --}}
                        <li><a href="" key=""></a></li>
                    </ul>
                </li>
                {{--  <li>
                    <a href="#" class="has-arrow waves-effect" aria-label="Department Setup Menu">
                        <i class="bx bxs-cog"></i>
                        <span key="t-setup">Department Setup</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="" key="t-default">KPI Setup</a></li>
                    </ul>
                </li>  --}}

                {{--  <li>
                    <a href="#" class="has-arrow waves-effect" aria-label="Department Setup Menu">
                        <i class="bx bxs-cog"></i>
                        <span key="t-setup">Department Setup</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="" key="t-default">KPI Setup</a></li>
                    </ul>
                </li>  --}}


                {{--  <li>
                    <a href="javascript: void(0);" class="has-arrow waves-effect" aria-label="Reports Menu">
                        <i class="bx bx-file"></i>
                        <span key="t-dashboards">Reports</span>
                    </a>
                    <ul class="sub-menu" aria-expanded="false">
                        <li><a href="">Overview</a></li>
                    </ul>
                </li>  --}}


                {{--  <hr style="margin: 25vh auto 1rem auto; width: 14rem;">  --}}

                {{--  Card for displaying support info  --}}
                {{--  <div class="card"
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
                            <a href="#" target="_blank" class="btn btn-primary">Coming Soon</a>
                        </div>
                    </div>
                </div>  --}}
            </ul>

        </div>
    </div>
    <!-- Sidebar -->
</div>
</div>
