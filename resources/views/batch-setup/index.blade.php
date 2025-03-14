<x-base-layout>
    <div class="container-fluid px-5">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Batch Setup</h4>
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
                        <th>Batch Name</th>
                        <th>Batch Code</th>
                        <th>Group</th>
                        <th>Unit</th>
                        <th>Year</th>
                        <th>State</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($batchData as $batch)
                        <tr>
                            <th scope="row"><a href="#">{{ $batch->name }}</a></th>
                            <td>{{ $batch->code }}</td>
                            <td><span class="dropdown badge rounded-pill bg-primary">{{ $batch->activityGroupName }}</span></td>
                            <td><span class="dropdown badge rounded-pill bg-secondary">{{ $batch->auditorUnitName }}</span></td>
                            <td>{{ $batch->year }}</td>

                            <td>
                                <span @style(['cursor: cursor'])
                                    class="dropdown badge rounded-pill {{ $batch->active == true ? 'bg-success' : 'bg-dark' }}"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    {{ $batch->active == true ? 'Active' : 'Deactivated' }}
                                    {{--  <div class="dropdown-menu">
                                        <a href="" class="dropdown-item" data-bs-toggle="modal"
                                            data-bs-target=".bs-example-modal-lg-" class="m-2">
                                            {{ $batch->active == true ? 'Deactivated' : 'Activate' }}
                                        </a>
                                    </div>  --}}
                                </span>
                                <div class="modal fade bs-example-modal-lg-" tabindex="-1" role="dialog"
                                    aria-labelledby="myLargeModalLabel" aria-hidden="true">
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

                                <span @style(['cursor: cursor'])
                                    class="dropdown badge rounded-pill {{ $batch->status == 'OPEN' ? 'bg-warning' : 'bg-dark' }}"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    {{ $batch->status == 'OPEN' ? 'OPEN' : 'CLOSED' }}
                                    {{--  <div class="dropdown-menu">
                                        <a href="" class="dropdown-item" data-bs-toggle="modal"
                                            data-bs-target=".bs-example-modal-lg-" class="m-2">
                                            {{ $batch->status == 'OPEN' ? 'CLOSED' : 'OPEN' }}
                                        </a>
                                    </div>  --}}
                                </span>


                                <!-- Modal for Confirmation -->
                                <div class="modal fade bs-status-modal-status-" tabindex="-1" role="dialog"
                                    aria-labelledby="myLargeModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-sm modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="myLargeModalLabel">Confirm Batch Status
                                                    Update</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">


                                                <form action="" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="status" value="">
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
                                    <a href="{{ route('batch.edit', $batch->id) }}">
                                        <span class="badge rounded-pill bg-primary fonte-size-13"><i
                                                class="bx bxs-pencil"></i>edit</span>
                                    </a>
                                    {{--  DELETE BUTTON  --}}
                                    <a href="" data-bs-toggle="modal" data-bs-target=".bs-delete-modal-lg-{{ $batch->id }}">
                                        <span class="badge rounded-pill bg-danger fonte-size-13"><i
                                                class="bx bxs-trash"></i> delete</span>
                                    </a>

                                    <!-- Modal for Delete Confirmation -->
                                    <div class="modal fade bs-delete-modal-lg-{{ $batch->id }}" tabindex="-1" role="dialog"
                                        aria-labelledby="myLargeModalLabel" aria-hidden="true">
                                        <div class="modal-dialog modal-lg modal-dialog-centered">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="myLargeModalLabel">Confirm Batch
                                                        Deletion</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <h4 class="text-center mb-4">Are you sure you want to delete this
                                                        batch?</h4>
                                                    <p class="text-center">Deleting a <b>batch</b> means removing it
                                                        from the <b>system entirely</b> and you cannot <b>recover</b> it
                                                        again</p>
                                                    <form action="{{ route('batch.delete', $batch->id) }}" method="POST">
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
                            <td colspan="7" class="text-center">No Batch Found</td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
            <nav aria-label="Page navigation example" class="mt-3">

            </nav>
        </div>



        <!-- right offcanvas -->
        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight"
            aria-labelledby="offcanvasRightLabel">
            <div class="offcanvas-header">
                <h5 id="offcanvasRightLabel">Batch Setup</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                    aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <form action="{{ route('batch.post') }}" method="POST">
                    @csrf
                    <div class="row mb-3">
                        <label for="example-text-input" class="">Batch Name</label>
                        <div class="col-md-12">
                            <input class="form-control" type="text" name="name" required
                                value="{{ old('name') }}" id="example-text-input">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="example-text-input" class="">Select Group</label>
                        <div class="col-md-12">
                            <select name="activityGroupId" class="form-select">
                                <option>Select group</option>
                                @foreach ($activeGroups as $batch)
                                    <option value="{{ $batch->id }}">{{ $batch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="example-text-input" class="">Select Unit Type</label>
                        <div class="col-md-12">
                            <select name="auditorUnitId" class="form-select">
                                <option>Select unit</option>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="example-text-input" class="">Batch Year</label>
                        <div class="col-md-12">
                            <select name="year" id="yearSelect" class="form-select">
                                <option>Select Year</option>
                                @php
                                    $currentYear = Carbon\Carbon::now()->year;
                                @endphp

                                <option value="{{ $currentYear }}">
                                    {{ $currentYear }}
                                </option>

                            </select>
                        </div>
                    </div>

                    <input type="hidden" name="active" value="1">
                    <input type="hidden" name="status" value="OPEN">
                    <button type="submit" class="btn btn-primary waves-effect waves-light col-md-12  mt-4">
                        Create
                    </button>
                </form>

            </div>
        </div>

    </div>




</x-base-layout>
