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
                        <th>Exception</th>
                        <th>Process Type</th>
                        <th>Department</th>
                        <th>Occurance Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>

                    <tr>
                        <th scope="row"><a href="#">Teller Operations</a></th>


                        <td>
                            <span class="dropdown badge rounded-pill bg-primary">
                                Teller and Cash
                            </span>
                        </td>
                        <td> Operations </td>
                        <td> <span
                                class="dropdown badge rounded-pill bg-success }}">
                                Pending Supervisor Approval
                            </span>
                        </td>
                        <td> 5th May, 2025
                        </td>

                        <td>
                            <div class="d-flex gap-3">
                                <a href="{{ route('exception.edit', 1) }}">
                                    <span class="badge round bg-primary font-size-13"><i
                                            class="bx bxs-pencil"></i>open</span>
                                </a>
                                {{--  DELETE BUTTON  --}}

                            </div>
                        </td>
                    </tr>


                </tbody>
            </table>

        </div>

    </div>




</x-base-layout>
