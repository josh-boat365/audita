<x-base-layout>




    <div class="container-fluid px-1">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">List of Exceptions For Approval From Supervisor</h4>
                </div>
            </div>
        </div>


        <div class="mt-4 mb-4" style="background-color: gray; height: 1px;"></div>


        <div class="table-responsive">
            <table class="table table-bordered  table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Auditor</th>
                        <th>Branch</th>
                        <th>Department</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pendingExceptions as $exception)
                        @php
                            $status = $exception['status'];
                            $tooltip = match ($status) {
                                'APPROVED' => 'This batch has a status of APPROVED but has some exceptions DECLINED',
                                'AMENDMENT'
                                    => 'This batch has a status of AMENDMENT but has some exceptions DECLINED/PENDING',
                                default => 'This batch has a status of DECLINED and it is just for VIEWING',
                            };

                            $badgeClass = match ($status) {
                                'APPROVED' => 'bg-dark',
                                'AMENDMENT' => 'bg-warning',
                                default => 'bg-danger',
                            };

                            $label = $status === 'APPROVED' ? 'DECLINED' : $status;
                        @endphp
                        <tr>
                            <th scope="row"><a href="#">{{ $exception['submittedBy'] }}</a></th>


                            <td>
                                <span class="dropdown badge rounded-pill bg-primary">
                                    {{ $exception['groupName'] }}
                                </span>
                            </td>
                            <td> {{ $exception['department'] }} <span class="dropdown badge rounded-pill bg-dark">
                                    {{ $exception['exceptionCount'] }}
                                </span> </td>
                            <td> {{ Carbon\Carbon::parse($exception['submittedAt'])->format('jS F, Y ') }} </td>
                            <td>
                                <span class="dropdown badge rounded-pill {{ $badgeClass }}" data-bs-toggle="tooltip"
                                    data-bs-placement="top" title="{{ $tooltip }}">
                                    {{ $label }}
                                </span>
                            </td>

                            <td>
                                <div class="d-flex gap-3">
                                    <a
                                        href="{{ url("/exception/auditor/show-exception-list-for-approval/{$exception['id']}/{$exception['status']}") }}">
                                        <span class="badge round bg-primary font-size-13"><i
                                                class="bx bxs-pencil"></i>open</span>
                                    </a>

                                </div>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bx bx-file fs-1 text-muted"></i>
                                <p class="mb-0">No pending exceptions for <b>APPROVAL</b></p>
                                <small>All exceptions have been processed</small>
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>

        </div>

    </div>




</x-base-layout>
