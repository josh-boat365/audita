<x-base-layout>

    <div class="container-fluid px-5">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18"> <a href="{{ route('members') }}">Dennis - Employee Id
                            ({{ $groupMember->employeeId }})</a> > Update
                        Group Members Detail
                    </h4>
                </div>
            </div>
        </div>
        <!-- end page title -->


        <div class="mt-4 mb-4" style="background-color: gray; height: 1px;"></div>

        <div class="row">
            <div class="col-md-12">
                <div class="card card-body">
                    <h3 class="card-title">Group Members Details</h3>
                    <form action="{{ route('members.update', $groupMember->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Select Group Member</label>
                            <div>
                                <select class="select2 form-control" id="customSelect" name="employeeId">
                                    <option selected> Select member......</option>
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee->id }}" @selected($employee->id === $groupMember->employeeId)>
                                            {{ $employee->firstName }},
                                            {{ $employee->surname }} - {{ $employee->empRoleName }}</option>
                                    @endforeach

                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Select Group</label>
                            <div>
                                <select class="select2 form-control" id="customSelect" name="activityGroupId">
                                    @foreach ($groups as $group)
                                        <option value="{{ $group->id }}" @selected($group->id === $groupMember->activityGroupId)>
                                            {{ $group->name }}
                                        </option>
                                    @endforeach

                                </select>
                            </div>
                        </div>



                        <button type="submit"
                            class="btn btn-success waves-effect waves-light col-md-12 mt-4">Update</button>
                    </form>

                </div>
            </div>
        </div>



    </div>




</x-base-layout>
