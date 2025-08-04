<x-base-layout>

    <div class="container-fluid px-5">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18"> <a href="{{ route('members') }}">List of members</a> >
                        Group Member Setup
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
                    <form action="{{ route('members.post') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Select Group Member</label>
                            <div>
                                <select class="select2 form-control select2-multiple" data-toggle="select2"
                                    multiple="multiple" data-placeholder="Select member........"  id="customSelect" name="employeeId[]">
                                    {{--  <option> Select member......</option>  --}}
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



    </div>




</x-base-layout>
