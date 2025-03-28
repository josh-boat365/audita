<x-base-layout>

    <div class="container-fluid px-5">
        {{--  {{ dd($batch_data) }}  --}}
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18"> <a href="{{ route('batch') }}">List of Batches</a> > Update
                        Batch Details > <a href="#">{{ $batch_data->name }}</a>
                    </h4>
                </div>
            </div>
        </div>
        <!-- end page title -->


        <div class="mt-4 mb-4" style="background-color: gray; height: 1px;"></div>

        <div class="row">
            <div class="col-md-12">
                <div class="card card-body">
                    <h3 class="card-title">Batch Details</h3>
                    <form action="{{ route('batch.update', $batch_data->id) }}" method="POST">
                        @csrf
                        <div class="row mb-3">
                            <label for="example-text-input" class="">Batch Name</label>
                            <div class="col-md-12">
                                <input class="form-control" type="text" name="name" required
                                    value="{{ $batch_data->name }}" id="example-text-input">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="example-text-input" class="">Batch Group</label>
                            <div class="col-md-12">
                                <select name="activityGroupId" id="yearSelect1" class="form-select">
                                    <option>Select Year</option>
                                    @foreach ($activityGroups as $group)
                                        <option value="{{ $group->id }}" @selected($batch_data->activityGroupId === $group->id)>
                                            {{ $group->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="example-text-input" class="">Batch Unit</label>
                            <div class="col-md-12">
                                <select name="auditorUnitId" id="yearSelect1" class="form-select">
                                    <option>Select Year</option>
                                    @foreach ($auditUnits as $unit)
                                        <option value="{{ $unit->id }}" @selected($batch_data->auditorUnitId === $unit->id)>
                                            {{ $unit->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>


                        <div class="row mb-3">
                            <label for="example-text-input" class="">Batch Year</label>
                            <div class="col-md-12">
                                <select name="year" id="yearSelect1" class="form-select">
                                    <option>Select Year</option>
                                    @php
                                        $currentYear = Carbon\Carbon::now()->year;
                                    @endphp
                                    <option value="{{ $currentYear }}" @selected($batch_data->year === "$currentYear")>
                                        {{ $currentYear }}
                                    </option>
                                </select>
                            </div>
                        </div>



                        <div class="row mb-3">
                            <label for="example-text-input" class="">Batch Status</label>
                            <div class="col-md-12">
                                <select class="form-control" id="statusSelect" name="status">
                                    <option value="OPEN" @selected($batch_data->status === 'OPEN')>
                                        OPEN
                                    </option>
                                    <option value="CLOSE" @selected($batch_data->status === 'CLOSE')>
                                        CLOSE
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="example-text-input" class="">Batch State</label>
                            <div class="col-md-12">
                                <select class="form-control" name="active">
                                    <option value="1" @selected($batch_data->active === true)>
                                        Active
                                    </option>
                                    <option value="0" @selected($batch_data->active === false)>
                                        Deactivate
                                    </option>
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
