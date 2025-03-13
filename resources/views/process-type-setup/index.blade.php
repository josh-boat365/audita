<x-base-layout>
    <div class="container-fluid px-5">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Process Type Setup</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div style="">

            <button type="button" class="btn btn-success btn-rounded waves-effect waves-light " data-bs-toggle="offcanvas"
                data-bs-target="#offcanvasRight" aria-controls="offcanvasRight"><i
                    class="bx bxs-plus"></i>Create</button>
        </div>
        <div class="mt-4 mb-4" style="background-color: gray; height: 1px;"></div>


        <div class="table-responsive">
            <table class="table table-borderless table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Process Type Name</th>
                        <th>State</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($processTypes as $processType)
                        <tr>
                            <th scope="row"><a href="#">{{ $processType->name }}</a></th>

                            <td>
                                <span @style(['cursor: pointer'])
                                    class="dropdown badge rounded-pill {{ $processType->active == true ? 'bg-success' : 'bg-dark' }}"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    {{ $processType->active == true ? 'Active' : 'Deactivated' }}
                                    <div class="dropdown-menu">
                                        <a href="" class="dropdown-item" data-bs-toggle="modal"
                                            data-bs-target=".bs-example-modal-lg-{{ $processType->id }}" class="m-2">
                                            {{ $processType->active == true ? 'Deactivated' : 'Activate' }}
                                        </a>
                                    </div>
                                </span>
                                <div class="modal fade bs-example-modal-lg-{{ $processType->id }}" tabindex="-1"
                                    role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-sm modal-dialog-centered ">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="myLargeModalLabel">Confirm Batch State
                                                    Update</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <h4 class="text-center mb-4"> Are you sure, you want to
                                                    ?</h4>
                                                <form action="" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="active" value="">
                                                    <div class="d-grid">
                                                        <button type="submit" class="btn btn-success">Yes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="d-flex gap-3">
                                    <a href="{{ route('process-type.edit', $processType->id) }}">
                                        <span class="badge rounded-pill bg-primary fonte-size-13"><i
                                                class="bx bxs-pencil"></i>edit</span>
                                    </a>
                                    {{--  DELETE BUTTON  --}}
                                    <a href="#" data-bs-toggle="modal"
                                        data-bs-target=".bs-delete-modal-lg-{{ $processType->id }}">
                                        <span class="badge rounded-pill bg-danger fonte-size-13"><i
                                                class="bx bxs-trash"></i> delete</span>
                                    </a>

                                    <!-- Modal for Delete Confirmation -->
                                    <div class="modal fade bs-delete-modal-lg-{{ $processType->id }}" tabindex="-1"
                                        role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-lg modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="myLargeModalLabel">Confirm Process Type
                                                        Deletion</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <h4 class="text-center mb-4">Are you sure you want to delete this
                                                        process type?</h4>
                                                    <p class="text-center">Deleting a <b>process type</b> means removing
                                                        it
                                                        from the <b>system entirely</b> and you cannot <b>recover</b> it
                                                        again</p>
                                                    <form action="{{ route('process-type.delete', $processType->id) }}"
                                                        method="POST">
                                                        @csrf

                                                        <div class="d-grid">
                                                            <button type="submit" class="btn btn-danger">Yes,
                                                                Delete</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="4" class="text-center">No Process Type Found</td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
            <nav aria-label="Page navigation example" class="mt-3">

            </nav>
        </div>



        <!-- right offcanvas -->
        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
            <div class="offcanvas-header">
                <h5 id="offcanvasRightLabel">Group Setup</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                    aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <form action="{{ route('process-type.post') }}" method="POST">
                    @csrf
                    <div class="row mb-3">
                        <label for="example-text-input" class="">Process Type Name</label>
                        <div class="col-md-12">
                            <input class="form-control" type="text" name="name" required
                                value="{{ old('name') }}" id="example-text-input">
                        </div>
                    </div>

                    <input type="hidden" name="active" value="1">
                    <button type="submit" class="btn btn-primary waves-effect waves-light col-md-12  mt-4">
                        Create
                    </button>
                </form>
            </div>
        </div>

    </div>




</x-base-layout>
