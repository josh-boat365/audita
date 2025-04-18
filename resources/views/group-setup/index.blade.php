<x-base-layout>
    <div class="container-fluid px-5">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Group Setup</h4>
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
                        <th>Group Name</th>
                        <th>Branch</th>
                        <th>State</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    {{--  {{ dd($groups) }}  --}}
                    @forelse ($groups as $group)
                        <tr>
                            <th scope="row"><a href="#">{{ $group->name }}</a></th>
                            <td class="col-3"> <span class="badge rounded-pill bg-dark">
                                    {{ $group->branchName }}
                                </span>

                            </td>

                            <td>
                                <span @style(['cursor: pointer'])
                                    class="dropdown badge rounded-pill {{ $group->active == true ? 'bg-success' : 'bg-dark' }}"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    Active
                                </span>
                            </td>

                            <td>
                                <div class="d-flex gap-3">
                                    <a href="{{ route('group.edit', $group->id) }}">
                                        <span class="badge rounded-pill bg-primary fonte-size-13"><i
                                                class="bx bxs-pencil"></i>edit</span>
                                    </a>
                                    {{--  {{ dd($group->createdBy .' name: '. $employeeFullName) }}  --}}
                                    @if ($group->createdBy == $employeeFullName)
                                        {{--  DELETE BUTTON  --}}
                                        <a href="#" data-bs-toggle="modal"
                                            data-bs-target=".bs-delete-modal-lg-{{ $group->id }}">
                                            <span class="badge rounded-pill bg-danger fonte-size-13"><i
                                                    class="bx bxs-trash"></i> delete</span>
                                        </a>

                                        <!-- Modal for Delete Confirmation -->
                                        <div class="modal fade bs-delete-modal-lg-{{ $group->id }}" tabindex="-1"
                                            role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="myLargeModalLabel">Confirm Group
                                                            Deletion</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <h4 class="text-center mb-4">Are you sure you want to delete
                                                            this
                                                            group?</h4>
                                                        <p class="text-center">Deleting a <b>group</b> means removing it
                                                            from the <b>system entirely</b> and you cannot
                                                            <b>recover</b> it
                                                            again</p>
                                                        <form action="{{ route('group.delete', $group->id) }}"
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
                                    @else
                                        <a href=""></a>
                                    @endif
                                </div>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="4" class="text-center">No Group Found</td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
            <nav aria-label="Page navigation example" class="mt-3">
                {{ $groups->links('pagination::bootstrap-5') }}
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
                <form action="{{ route('group.post') }}" method="POST">
                    @csrf
                    <div class="row mb-3">
                        <label for="example-text-input" class="">Group Name</label>
                        <div class="col-md-12">
                            <input class="form-control" type="text" name="name" required
                                value="{{ old('name') }}" id="example-text-input">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label for="example-text-input" class="">Branch</label>
                        <div class="col-md-12">
                            <select name="branch_id" class="form-select">
                                <option>Select Branch</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
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
