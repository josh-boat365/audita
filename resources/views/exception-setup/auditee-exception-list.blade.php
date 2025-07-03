<x-base-layout>
    @php
        $status = 'APPROVED';
    @endphp

    <!-- Add SweetAlert2 CSS in the head section -->
    @push('styles')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.32/sweetalert2.min.css">
    @endpush

    <div class="container-fluid px-1">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">List of Exceptions For Branch</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="mt-4 mb-4" style="background-color: gray; height: 1px;"></div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover mb-0">
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
                        <tr>
                            <th scope="row"><a href="#">{{ $exception['submittedBy'] }}</a></th>

                            <td>
                                <span class="dropdown badge rounded-pill bg-primary">
                                    {{ $exception['groupName'] }}
                                </span>
                            </td>
                            <td>
                                <div>
                                    {{ $exception['department'] }}
                                    <span class="dropdown badge rounded-pill bg-dark">
                                        {{ $exception['exceptionCount'] }}
                                    </span>
                                </div>
                                <div>
                                    <p class="badge badge-soft-secondary">
                                        <b>{{ $exception['countForRespondedExceptionsByAuditee'] }}</b> responded
                                        exception(s) of <b>{{ $exception['exceptionCount'] }}</b> total exception(s)
                                    </p>
                                </div>
                            </td>
                            <td> {{ Carbon\Carbon::parse($exception['submittedAt'])->format('jS F, Y ') }} </td>
                            <td> <span class="dropdown badge rounded-pill bg-success ">
                                    {{ $exception['status'] === 'APPROVED' ? 'PENDING AUDITOR APPROVAL' : $exception['status'] }}
                                </span>
                            </td>

                            <td>
                                <div class="d-flex gap-3">
                                    <a
                                        href="{{ url("/exception/supervisor/show-exception-list-for-approval/{$exception['id']}/{$exception['status']}") }}">
                                        <span class="badge round bg-primary font-size-13"><i
                                                class="bx bxs-pencil"></i>open</span>
                                    </a>
                                    @if (in_array($exception['auditorDepartmentId'], [7, 8]) &&
                                            $exception['countForRespondedExceptionsByAuditee'] === $exception['exceptionCount']
                                    )
                                        {{--  7 = audit department, 8 = internal control  --}}
                                        <form class="exception-form" action="{{ route('exception.supervisor.action') }}"
                                            method="POST" data-exception-id="{{ $exception['id'] }}"
                                            data-department="{{ $exception['department'] }}"
                                            data-branch="{{ $exception['groupName'] }}">
                                            @csrf
                                            <input type="hidden" name="batchExceptionId"
                                                value="{{ $exception['id'] }}">
                                            <input type="hidden" name="status" value="ANALYSIS">
                                            <button type="submit" class="badge round bg-dark font-size-13">
                                                <i class="bx bx-analyse"></i> Push for Resolved
                                            </button>
                                        </form>
                                    @else
                                        <div></div>
                                    @endif
                                </div>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bx bx-file fs-1 text-muted"></i>
                                <p class="mb-0">No pending exceptions for <b>RESPONSE</b> from <b>AUDITOR</b></p>
                                <small>All exceptions have been processed</small>
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>
    </div>

    <!-- Add SweetAlert2 JS and custom script -->
    @push('scripts')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/limonte-sweetalert2/11.7.32/sweetalert2.all.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Add event listeners to all exception forms
                document.querySelectorAll('.exception-form').forEach(function(form) {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();

                        const formElement = this;
                        const formData = new FormData(formElement);
                        const exceptionId = formElement.dataset.exceptionId;
                        const department = formElement.dataset.department;
                        const branch = formElement.dataset.branch;

                        // Show confirmation modal
                        Swal.fire({
                            title: 'Confirm Action',
                            html: `
                                <div class="text-start">
                                    <p><strong>Are you sure you want to push this exception for resolution?</strong></p>
                                    <hr>
                                    <p><strong>Exception ID:</strong> ${exceptionId}</p>
                                    <p><strong>Branch:</strong> ${branch}</p>
                                    <p><strong>Department:</strong> ${department}</p>
                                </div>
                            `,
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: '<i class="bx bx-check"></i> Yes, Push for Resolution',
                            cancelButtonText: '<i class="bx bx-x"></i> Cancel',
                            reverseButtons: true,
                            customClass: {
                                confirmButton: 'btn btn-primary me-2',
                                cancelButton: 'btn btn-secondary'
                            },
                            buttonsStyling: false
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Show loading state
                                Swal.fire({
                                    title: 'Processing Request...',
                                    html: '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Please wait while we process your request.</p></div>',
                                    allowOutsideClick: false,
                                    allowEscapeKey: false,
                                    showConfirmButton: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                    }
                                });

                                // Make AJAX request
                                fetch(formElement.action, {
                                        method: 'POST',
                                        body: formData,
                                        headers: {
                                            'X-Requested-With': 'XMLHttpRequest',
                                            'Accept': 'application/json'
                                        }
                                    })
                                    .then(response => {
                                        if (!response.ok) {
                                            throw new Error(
                                                `HTTP error! status: ${response.status}`
                                            );
                                        }
                                        return response.json();
                                    })
                                    .then(data => {
                                        if (data.success) {
                                            Swal.fire({
                                                title: 'Success!',
                                                text: data.message ||
                                                    'Exception has been successfully pushed for resolution.',
                                                icon: 'success',
                                                confirmButtonText: 'OK',
                                                customClass: {
                                                    confirmButton: 'btn btn-success'
                                                },
                                                buttonsStyling: false
                                            }).then(() => {
                                                // Reload the page to reflect changes
                                                window.location.reload();
                                            });
                                        } else {
                                            throw new Error(data.message ||
                                                'An unexpected error occurred');
                                        }
                                    })
                                    .catch(error => {
                                        console.error('Error:', error);
                                        Swal.fire({
                                            title: 'Error!',
                                            text: error.message ||
                                                'Something went wrong. Please try again.',
                                            icon: 'error',
                                            confirmButtonText: 'Try Again',
                                            customClass: {
                                                confirmButton: 'btn btn-danger'
                                            },
                                            buttonsStyling: false
                                        });
                                    });
                            }
                        });
                    });
                });
            });
        </script>
    @endpush

</x-base-layout>
