<x-base-layout>

    <div class="container-fluid px-5">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18"> <a href="{{ route('process-type') }}">Process types</a> > Update Process Type Details
                        > <a href="#">{{ $processType->name }}</a>
                    </h4>
                </div>
            </div>
        </div>
        <!-- end page title -->


        <div class="mt-4 mb-4" style="background-color: gray; height: 1px;"></div>

        <div class="row">
            <div class="col-md-12">
                <div class="card card-body">
                    <h3 class="card-title">Process Type Details</h3>
                    <form action="{{ route('process-type.update', $processType->id) }}" method="POST">
                        @csrf
                        <div class="row mb-3">
                            <label for="example-text-input" class="">Process Type Name</label>
                            <div class="col-md-12">
                                <input class="form-control" type="text" name="name" required value="{{ $processType->name }}"
                                    id="example-text-input">
                            </div>
                        </div>


                        <div class="row mb-3">
                            <label for="example-text-input" class="">Process Type State</label>
                            <div class="col-md-12">
                                <select class="form-control" name="active">
                                    <option value="1" @selected($processType->active === true)>
                                        Active
                                    </option>
                                    <option value="0" @selected($processType->active === false)>
                                        Deactivate
                                    </option>
                                </select>

                                {{--  <input type="hidden" name="stateHidden" id="stateHidden" value="">  --}}


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
