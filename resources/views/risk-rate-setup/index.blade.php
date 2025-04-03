<x-base-layout>
    <div class="container-fluid px-5">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Risk Rate Setup</h4>
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
            <table class="table table-bordered  table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Risk Rate Name</th>
                        <th>State</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($riskRates as $riskRate)
                        <tr>
                            <th scope="row"><a href="#">{{ $riskRate->name }}</a></th>

                            <td>
                                <span @style(['cursor: pointer'])
                                    class="dropdown badge rounded-pill {{ $riskRate->active == true ? 'bg-success' : 'bg-dark' }}"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    {{ $riskRate->active == true ? 'Active' : 'Deactivated' }}
                                    <div class="dropdown-menu">
                                        <a href="" class="dropdown-item" data-bs-toggle="modal"
                                            data-bs-target=".bs-example-modal-lg-{{ $riskRate->id }}" class="m-2">
                                            {{ $riskRate->active == true ? 'Deactivated' : 'Activate' }}
                                        </a>
                                    </div>
                                </span>
                                <div class="modal fade bs-example-modal-lg-{{ $riskRate->id }}" tabindex="-1"
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
                                    <a href="{{ route('risk-rate.edit', $riskRate->id) }}">
                                        <span class="badge rounded-pill bg-primary fonte-size-13"><i
                                                class="bx bxs-pencil"></i>edit</span>
                                    </a>
                                    {{--  DELETE BUTTON  --}}
                                    <a href="#" data-bs-toggle="modal"
                                        data-bs-target=".bs-delete-modal-lg-{{ $riskRate->id }}">
                                        <span class="badge rounded-pill bg-danger fonte-size-13"><i
                                                class="bx bxs-trash"></i> delete</span>
                                    </a>

                                    <!-- Modal for Delete Confirmation -->
                                    <div class="modal fade bs-delete-modal-lg-{{ $riskRate->id }}" tabindex="-1"
                                        role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-lg modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="myLargeModalLabel">Confirm Risk Rate
                                                        Deletion</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <h4 class="text-center mb-4">Are you sure you want to delete this
                                                        risk rate?</h4>
                                                    <p class="text-center">Deleting a <b>risk rate</b> means removing
                                                        it
                                                        from the <b>system entirely</b> and you cannot <b>recover</b> it
                                                        again</p>
                                                    <form action="{{ route('risk-rate.delete', $riskRate->id) }}"
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
                            <td colspan="4" class="text-center">No Risk Rate Found</td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
            <nav aria-label="Page navigation example" class="mt-3">
                {{ $riskRates->links('pagination::bootstrap-5') }}
            </nav>
        </div>



        <!-- right offcanvas -->
        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
            <div class="offcanvas-header">
                <h5 id="offcanvasRightLabel">Risk Rate Setup</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                    aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <form action="{{ route('risk-rate.post') }}" method="POST">
                    @csrf
                    <div class="row mb-3">
                        <label for="example-text-input" class="">Risk Rate Name</label>
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
