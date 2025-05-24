<x-base-layout>
    <div class="container-fluid px-1">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">List of Exceptions For Approval</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        {{--  <a href="{{ route('exception.create') }}" class="btn btn-success btn-rounded waves-effect waves-light "><i
                class="bx bxs-plus"></i>Create</a>  --}}

        <div class="mt-4 mb-4" style="background-color: gray; height: 1px;"></div>


        <div class="table-responsive">
            <table class="table table-bordered  table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Auditor</th>
                        <th>Branch</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>

                    <tr>
                        <th scope="row"><a href="#">Micheal Asenso</a></th>


                        <td>
                            <span class="dropdown badge rounded-pill bg-primary">
                                Sweduru
                            </span>
                        </td>
                        <td> Operations </td>
                        <td> <span class="dropdown badge rounded-pill bg-success }}">
                                Pending Supervisor Approval
                            </span>
                        </td>

                        <td>
                            <div class="d-flex gap-3">
                                <a href="{{ route('show.branch.exception.for.approval') }}">
                                    <span class="badge round bg-primary font-size-13"><i
                                            class="bx bxs-pencil"></i>open</span>
                                </a>

                            </div>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><a href="#">Frimpong Agyei</a></th>


                        <td>
                            <span class="dropdown badge rounded-pill bg-primary">
                                Sweduru
                            </span>
                        </td>
                        <td> Finance </td>
                        <td> <span class="dropdown badge rounded-pill bg-success }}">
                                Pending Supervisor Approval
                            </span>
                        </td>

                        <td>
                            <div class="d-flex gap-3">
                                <a href="{{ route('show.branch.exception.for.approval') }}">
                                    <span class="badge round bg-primary font-size-13"><i
                                            class="bx bxs-pencil"></i>open</span>
                                </a>

                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><a href="#">Dennis Appiah</a></th>


                        <td>
                            <span class="dropdown badge rounded-pill bg-primary">
                                Sweduru
                            </span>
                        </td>
                        <td> Business </td>
                        <td> <span class="dropdown badge rounded-pill bg-success }}">
                                Pending Supervisor Approval
                            </span>
                        </td>

                        <td>
                            <div class="d-flex gap-3">
                                <a href="{{ route('show.branch.exception.for.approval') }}">
                                    <span class="badge round bg-primary font-size-13"><i
                                            class="bx bxs-pencil"></i>open</span>
                                </a>

                            </div>
                        </td>
                    </tr>


                </tbody>
            </table>

        </div>

    </div>




</x-base-layout>
