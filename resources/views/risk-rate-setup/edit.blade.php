<x-base-layout>

    <div class="container-fluid px-5">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18"> <a href="{{ route('risk-rate') }}">{{ $riskRate->name }}</a> > Update Process Type Details
                    </h4>
                </div>
            </div>
        </div>
        <!-- end page title -->


        <div class="mt-4 mb-4" style="background-color: gray; height: 1px;"></div>

        <div class="row">
            <div class="col-md-12">
                <div class="card card-body">
                    <h3 class="card-title">Risk Rate Details</h3>
                    <form action="{{ route('risk-rate.update', $riskRate->id) }}" method="POST">
                        @csrf
                        <div class="row mb-3">
                            <label for="example-text-input" class="">Risk Rate Name</label>
                            <div class="col-md-12">
                                <input class="form-control" type="text" name="name" required value="{{ $riskRate->name }}"
                                    id="example-text-input">
                            </div>
                        </div>


                        <div class="row mb-3">
                            <label for="example-text-input" class="">Risk Rate State</label>
                            <div class="col-md-12">
                                <select class="form-control" name="active">
                                    <option value="1" @selected($riskRate->active === true)>
                                        Active
                                    </option>
                                    <option value="0" @selected($riskRate->active === false)>
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
