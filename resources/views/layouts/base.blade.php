<!doctype html>
<html lang="en">

@include('layouts.head')

<body data-sidebar="dark">

    <!-- <body data-layout="horizontal" data-topbar="dark"> -->

    <!-- Begin page -->
    <div id="layout-wrapper">

        @include('sweetalert::alert')


        @include('layouts.dash-nav')

        <!-- ========== Left Sidebar Start ========== -->
        @include('layouts.left-side-nav', [
            'employeeId' => $employeeId,
            'employeeRoleId' => $employeeRoleId,
            'topManagers' => $topManagers,
            'auditorDepartments' => $auditorDepartments,
            'employeeDepartmentId' => $employeeDepartmentId,
            'pending_exception_count' => $pending_exception_count,
        ])

        <!-- Left Sidebar End -->

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->

        <div class="main-content">

            <div class="page-content">
                {{ $slot }}
                <!-- container-fluid -->
            </div>
            <!-- End Page-content -->



            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">
                            2025 -
                            <script>
                                document.write(new Date().getFullYear())
                            </script> © Audita | BPSL - IT Support.
                        </div>
                        <div class="col-sm-6">
                            <div class="text-sm-end d-none d-sm-block">

                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
        <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->

    @include('layouts.footer')
    @stack('scripts')
</body>


</html>
