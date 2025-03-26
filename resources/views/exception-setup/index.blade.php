<x-base-layout>
    <div class="container-fluid px-5">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">List of Exceptions</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <a href="{{ route('exception.create') }}" class="btn btn-success btn-rounded waves-effect waves-light "><i
                class="bx bxs-plus"></i>Create</a>

        <div class="mt-4 mb-4" style="background-color: gray; height: 1px;"></div>


        <div class="table-responsive">
            <table class="table table-borderless table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Exception</th>
                        <th>Root Cause</th>
                        <th>Auditor</th>
                        <th>Process Type</th>
                        <th>Risk Rate</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Occurrence Date</th>
                        <th>Proposed Resolution Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($exceptions as $exception)
                        <tr>
                            <th scope="row"><a href="#">{{ $exception->exception }}</a></th>
                            <td> {{ $exception->rootCause }} </td>
                            <td> {{ $exception->auditorName }} </td>

                            <td>
                                <span class="dropdown badge rounded-pill bg-primary">
                                    {{ $exception->processType }}
                                </span>
                            </td>
                            <td>
                                <span
                                    class="dropdown badge rounded-pill {{ $exception->riskRate == 'High' ? 'bg-danger' : ($exception->riskRate == 'Medium' ? 'bg-warning' : 'bg-success') }}">
                                    {{ $exception->riskRate }}
                                </span>

                            </td>
                            <td> {{ $exception->department }} </td>
                            <td> <span
                                    class="dropdown badge rounded-pill {{ $exception->status == 'PENDING' ? 'bg-dark' : 'bg-success' }}">
                                    {{ $exception->status }}
                                </span>
                            </td>
                            <td> {{ Carbon\Carbon::parse($exception->occurrenceDate)->format('jS F, Y ') }}
                            </td>
                            <td> {{ Carbon\Carbon::parse($exception->proposeResolutionDate)->format('jS F, Y ') }}
                            </td>


                            <td>
                                <div class="d-flex gap-3">
                                    <a href="{{ route('exception.edit', $exception->id) }}">
                                        <span class="badge rounded-pill bg-primary fonte-size-13"><i
                                                class="bx bxs-pencil"></i>open</span>
                                    </a>
                                    {{--  DELETE BUTTON  --}}
                                    @if ($exception->auditorId === $employeeId)
                                        <a href="" data-bs-toggle="modal"
                                            data-bs-target=".bs-delete-modal-lg-{{ $exception->id }}">
                                            <span class="badge rounded-pill bg-danger fonte-size-13"><i
                                                    class="bx bxs-trash"></i> delete</span>
                                        </a>
                                        <!-- Modal for Delete Confirmation -->
                                        <div class="modal fade bs-delete-modal-lg-{{ $exception->id }}" tabindex="-1"
                                            role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="myLargeModalLabel">Confirm Batch
                                                            Deletion</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <h4 class="text-center mb-4">Are you sure you want to delete
                                                            this
                                                            batch?</h4>
                                                        <p class="text-center">Deleting a <b>batch</b> means removing it
                                                            from the <b>system entirely</b> and you cannot
                                                            <b>recover</b> it
                                                            again
                                                        </p>
                                                        <form action="{{ route('exception.delete', $exception->id) }}"
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
                                        <p></p>
                                    @endif

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

    </div>




</x-base-layout>
