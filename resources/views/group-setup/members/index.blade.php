<x-base-layout>
    <div class="container-fluid px-5">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Group Members Setup</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div style="">

            <a href="{{ route('members.create') }}" class="btn btn-success btn-rounded waves-effect waves-light "><i
                    class="bx bxs-plus"></i>Create</a>
        </div>
        <div class="mt-4 mb-4" style="background-color: gray; height: 1px;"></div>


        <div class="table-responsive">
            <table class="table table-borderless table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Group Name</th>
                        <th>Branch</th>
                        <th>Members</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($groupedMembers as $groupData)
                        @php
                            $group = $groupData['group'];
                            $members = $groupData['members'];
                        @endphp

                        <!-- Group Header Row -->
                        <tr class="group-header bg-light">
                            <td colspan="4">
                                <strong>{{ $group->name ?? 'Unnamed Group' }}</strong>
                                <span class="badge bg-dark ms-2">{{ count($members) }} members</span>
                            </td>
                        </tr>

                        <!-- Member Rows -->
                        @foreach ($members as $member)
                            <tr class="group-member">
                                <td></td> <!-- Empty cell under Group Name -->
                                <td>{{ $group->branchName ?? 'N/A' }}</td>
                                <td>
                                    <a href="#">
                                        {{ $member->employeeName }}
                                    </a>
                                </td>
                                <td>
                                    <div class="d-flex gap-3">
                                        <a href="{{ route('members.edit', $member->id) }}">
                                            <span class="badge rounded-pill bg-primary fonte-size-13">
                                                <i class="bx bxs-pencil"></i> edit
                                            </span>
                                        </a>
                                        <a href="#" data-bs-toggle="modal"
                                            data-bs-target=".bs-delete-modal-lg-{{ $member->id }}">
                                            <span class="badge rounded-pill bg-danger fonte-size-13">
                                                <i class="bx bxs-trash"></i> delete
                                            </span>
                                        </a>

                                        <!-- Modal for Delete Confirmation -->
                                        <div class="modal fade bs-delete-modal-lg-{{ $member->id }}" tabindex="-1"
                                            role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="myLargeModalLabel">Confirm Member
                                                            Deletion</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <h4 class="text-center mb-4">Are you sure you want to delete
                                                            this member?</h4>
                                                        <p class="text-center">Deleting a <b>member</b> means removing
                                                            them from the group.</p>
                                                        <form action="{{ route('members.delete', $member->id) }}"
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
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">No Groups Found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Maintain your existing pagination -->
            <nav aria-label="Page navigation example" class="mt-3">
                {{ $paginator->links('pagination::bootstrap-5') }}
            </nav>
        </div>

        <style>
            .group-header {
                background-color: #f8f9fa !important;
                font-weight: bold;
            }

            .group-header td {
                padding: 12px 15px !important;
                border-bottom: 2px solid #dee2e6;
            }

            .group-member td {
                padding: 8px 15px !important;
                border-bottom: 1px solid #eee;
            }

            .group-member:last-child td {
                border-bottom: 2px solid #dee2e6;
            }

            .group-member td:first-child {
                padding-left: 30px !important;
            }

            .table-hover .group-member:hover td {
                background-color: rgba(0, 0, 0, 0.03);
            }
        </style>



        <!-- right offcanvas -->
        <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
            <div class="offcanvas-header">
                <h5 id="offcanvasRightLabel">Group Members Setup</h5>
                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                    aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <form action="{{ route('members.post') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Select Group Member</label>
                        <div>
                            <select class="select2 form-control select2-multiple" multiple="multiple"
                                data-placeholder="Choose ..." id="customSelect" name="employeeId">
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->firstName }},
                                        {{ $employee->surname }} - {{ $employee->empRoleName }}</option>
                                @endforeach

                            </select>
                        </div>
                    </div>


                    <div class="row mb-3">
                        <label for="activityGroupId">Select Group</label>
                        <div class="col-md-12">
                            <select name="activityGroupId" class="form-control">
                                <option>Select group</option>
                                @foreach ($groups as $group)
                                    <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach

                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary waves-effect waves-light col-md-12  mt-4">
                        Create
                    </button>
                </form>
            </div>
        </div>

    </div>




</x-base-layout>
