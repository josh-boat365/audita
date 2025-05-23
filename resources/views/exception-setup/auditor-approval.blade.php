<x-base-layout>

    <!-- FilePond CSS -->
    <link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    @if (URL::current() == route('exception.edit', $exception->id))
                        <h4 class="mb-sm-0 font-size-18"> <a href="{{ route('exception.list') }}">
                                List of Exceptions </a> > Update Exception > <a
                                href="#">exception</a>
                        </h4>
                    @else
                        <h4 class="mb-sm-0 font-size-18"> <a href="{{ route('exception.pending') }}">
                                List of Pending Exceptions </a> > Update Exception > <a
                                href="#">{{ $exception->exception }}</a>
                        </h4>
                    @endif
                </div>
            </div>
        </div>
        <!-- end page title -->


        <div class="mt-4 mb-4" style="background-color: gray; height: 1px;"></div>

        <ul class="nav nav-tabs nav-tabs-custom nav-justified" role="tablist">
            <li class="nav-item">
                <a class="nav-link active border border-3" data-bs-toggle="tab" href="#exception-creation"
                    role="tab">
                    <span class="d-block d-sm-none"><i class="fas fa-file-alt"></i></span>
                    <span class="d-none d-sm-block">Exception Update</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link border border-3" data-bs-toggle="tab" href="#file-attachments" role="tab">
                    <span class="d-block d-sm-none"><i class="fas fa-paperclip"></i></span>
                    <span class="d-none d-sm-block">Attach Files</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link border border-3" data-bs-toggle="tab" href="#chats-comments" role="tab">
                    <span class="d-block d-sm-none"><i class="fas fa-paperclip"></i></span>
                    <span class="d-none d-sm-block">Chats & Comments</span>
                </a>
            </li>
            @if ($exception->auditorId == $employeeId || in_array($employeeDepartmentId, [7, 8]))
                <li class="nav-item">
                    <a class="nav-link border border-3" data-bs-toggle="tab" href="#close-exception" role="tab">
                        <span class="d-block d-sm-none"><i class="fas fa-paperclip"></i></span>
                        <span class="d-none d-sm-block">Close Exception</span>
                    </a>
                </li>
            @else
                <li class="nav-item">
                    <a class="nav-link border border-3" data-bs-toggle="tab" href="#recommend-resolution"
                        role="tab">
                        <span class="d-block d-sm-none"><i class="fas fa-paperclip"></i></span>
                        <span class="d-none d-sm-block">Auditee Exception Closure</span>
                    </a>
                </li>
            @endif

        </ul>

        <div class="tab-content p-3 text-muted">
            <div class="tab-pane active" id="exception-creation" role="tabpanel">

                <form action="{{ route('exception.update', $exception->id) }}" method="POST"
                    enctype="multipart/form-data" autocomplete="on" class="needs-validation">
                    @csrf
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-body">

                                    <div class="mb-3">
                                        <label class="form-label">Exception<span class="required">*</span></label>
                                        <textarea @disabled(!$canEdit) class="form-control" rows="3" id="exception" name="exception"
                                            placeholder="Enter exception details......" required>{{ $exception->exception }}</textarea>
                                        <div class="invalid-feedback">Please enter an exception.</div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Root Cause</label>
                                        <textarea @disabled(!$canEdit) class="form-control" rows="3" name="rootCause"
                                            placeholder="Enter root cause details......">{{ $exception->rootCause }}</textarea>
                                        <div class="invalid-feedback">Please enter the root cause.</div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Batch<span class="required">*</span></label>
                                        <select @disabled(!$canEdit) class="form-select select2"
                                            name="exceptionBatchId" required>
                                            <option>Select.....</option>
                                            @foreach ($batches as $batch)
                                                <option value="{{ $batch->id }}" @selected($batch->id === $exception->exceptionBatchId)>
                                                    {{ $batch->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Unit/Dept<span class="required">*</span></label>
                                        <select @disabled(!$canEdit)class="form-select select2"
                                            name="departmentId" required>
                                            <option>Select Unit/Department</option>
                                            @foreach ($departments as $department)
                                                <option value="{{ $department->id }}" @selected($department->id === $exception->departmentId)>
                                                    {{ $department->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Occurrence Date<span
                                                class="required">*</span></label>
                                        <input @disabled(!$canEdit)type="text" class="form-control"
                                            placeholder="Select occurrence date" name="occurrenceDate"
                                            value="{{ $exception->occurrenceDate == null ? '' : Carbon\Carbon::parse($exception->occurrenceDate)->format('d/m/Y') }}"
                                            data-date-format="d/m/yy" data-provide="datepicker"
                                            data-date-autoclose="true" required />
                                        <div class="invalid-feedback">Please select occurrence date.</div>
                                    </div>
                                </div>
                                <!-- end card body -->
                            </div>
                            <!-- end card -->

                        </div>
                        <!-- end col -->
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title mb-3">Actions</h5>

                                    <div class="mb-3">
                                        <label class="form-label" for="project-status-input">Status</label>
                                        <select @disabled(!$canEdit)class="form-select" name="status">
                                            <option selected>Select.....</option>
                                            <option value="PENDING" @selected($exception->status === 'PENDING')>Pending</option>
                                            <option value="RESOLVED" @selected($exception->status === 'RESOLVED')>Resolved</option>
                                        </select>
                                        <div class="invalid-feedback">Please select exception status.</div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="project-visibility-input">Risk Rate</label>
                                        <select @disabled(!$canEdit)class="form-select select2"
                                            name="riskRateId">
                                            <option>Select.....</option>
                                            @foreach ($riskRates as $riskRate)
                                                <option value="{{ $riskRate->id }}" @selected($riskRate->id === $exception->riskRateId)>
                                                    {{ $riskRate->name }}</option>
                                            @endforeach

                                        </select>
                                    </div>

                                    <div>
                                        <label class="form-label" for="project-visibility-input">Process
                                            Type/Scope<span class="required">*</span></label>
                                        <select @disabled(!$canEdit)class="form-select select2"
                                            name="processTypeId" required>
                                            <option >Select.....</option>
                                            @foreach ($processTypes as $processType)
                                                <option value="{{ $processType->id }}" @selected($processType->id === $exception->processTypeId)>
                                                    {{ $processType->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>


                                </div>
                                <!-- end card body -->
                            </div>
                            <!-- end card -->

                            <div class="card">
                                <div class="card-body">


                                    <div class="mb-3">
                                        <label class="form-label">Proposed Resolution Date</label>
                                        <small>(optional)</small>
                                        <input @disabled(!$canEdit)type="text" class="form-control"
                                            placeholder="Select due date" name="proposeResolutionDate"
                                            value="{{ $exception->proposeResolutionDate == null ? '' : Carbon\Carbon::parse($exception->proposeResolutionDate)->format('d/m/Y') }}"
                                            data-date-format="d/m/yy" data-provide="datepicker"
                                            data-date-autoclose="true" />
                                        <div class="invalid-feedback">Please select proposed resolution date.</div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Resolution Date</label>
                                        <small>(optional)</small>
                                        <input @disabled(!$canEdit) type="text" class="form-control"
                                            placeholder="Select resolution date" name="resolutionDate"
                                            value="{{ $exception->resolutionDate == null ? '' : Carbon\Carbon::parse($exception->resolutionDate)->format('d/m/Y') }}"
                                            data-date-format="d/m/yy" data-provide="datepicker"
                                            data-date-autoclose="true"  />
                                        <div class="invalid-feedback">Please select resolution date.</div>
                                    </div>
                                </div>
                                <!-- end card body -->
                            </div>
                            <!-- end card -->
                        </div>
                        <!-- end col -->

                        <div class="col-lg-8">
                            <div class="text-end mb-4">
                                <button type="submit" class="btn btn-primary">Update Exception</button>
                            </div>
                        </div>
                    </div>
                    <!-- end row -->
                </form>
            </div>

            {{-- FILE UPLOAD --}}
            <div class="tab-pane" id="file-attachments" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        <form id="file-upload-form" action="{{ route('exception.file.upload', $exception->id) }}"
                            method="POST" enctype="multipart/form-data">
                            @csrf
                            <label class="form-label">Attach Files</label>
                            <div class="dropzone" id="myId">
                                <div class="dz-message needsclick">
                                    <div class="mb-3">
                                        <i class="display-4 text-muted bx bxs-cloud-upload"></i>
                                    </div>
                                    <h4>Drop files here or click to upload.</h4>
                                </div>
                            </div>
                            <div class="mt-4 text-end">
                                <button type="button" id="upload-button" class="btn btn-primary">Upload
                                    File</button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- FILE PREVIEWS --}}
                <div class="card">
                    <div class="card-body">
                        <h4>Files</h4>
                        <div class="mt-4 mb-4" style="background-color: gray; height: 1px;"></div>
                        <div class="row">
                            @forelse ($exception->fileAttached as $file)
                                <div class="col-xl-4 mb-3" id="file-{{ $file->id }}">
                                    <div class="file-info p-3 border rounded">
                                        <p><strong>{{ $file->fileName }}</strong></p>
                                        <p class="text-muted">{{ $file->uploadDate }}</p>
                                        <p class="text-muted"><strong>Uploaded By: </strong>{{ $file->uploadedBy }}
                                        </p>

                                        <div class="d-flex justify-content-between">
                                            <!-- Download Button -->
                                            <button onclick="downloadFile('{{ $file->id }}')"
                                                class="btn btn-sm btn-primary">
                                                <i class="fas fa-download"></i> Download
                                            </button>

                                            <!-- Delete Button -->
                                            @if ($file->uploadedBy == $employeeName)
                                                <button onclick="deleteFile('{{ $file->id }}')"
                                                    class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i> Remove
                                                </button>
                                            @else
                                                <div></div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <p>No files uploaded Yet</p>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>




            {{--  CHATS  --}}

            <div class="tab-pane" id="chats-comments" role="tabpanel">
                <div class="w-100 user-chat">
                    <div class="card">
                        <div class="p-4 border-bottom ">
                            <div class="row">
                                <div class="col-md-4 col-9">
                                    <h5 class="font-size-15 mb-1">{{ session('user_name') }}</h5>
                                    <p class="text-muted mb-0"><i
                                            class="mdi mdi-circle text-success align-middle me-1"></i> Active now</p>
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="chat-conversation p-3">
                                <ul class="list-unstyled mb-0" data-simplebar style="max-height: 486px;">
                                    @php
                                        $sortedComments = collect($exception->comment);
                                        //->sortByDesc('createdAt')
                                    @endphp

                                    @forelse ($sortedComments as $comment)
                                        @if ($comment->createdBy != $employeeName)
                                            {{--  COMMENT FROM OTHER USERS  --}}
                                            <li class="left">
                                                <div class="conversation-list">
                                                    <div class="dropdown">
                                                        @if ($comment->createdBy == $employeeName)
                                                            <a class="dropdown-toggle" href="#" role="button"
                                                                data-bs-toggle="dropdown" aria-haspopup="true"
                                                                aria-expanded="false">
                                                                <i class="bx bx-dots-vertical-rounded"></i>
                                                            </a>
                                                            <div class="dropdown-menu">
                                                                <a class="dropdown-item" href="#"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#bs-edit-left-modal-lg-{{ $comment->id }}">Edit</a>
                                                                <a class="dropdown-item" href="#"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#bs-delete-left-modal-lg-{{ $comment->id }}">Delete</a>
                                                            </div>
                                                        @else
                                                            <div></div>
                                                        @endif
                                                    </div>

                                                    <div class="ctext-wrap">
                                                        <div class="conversation-name">{{ $comment->createdBy }}</div>
                                                        <p>{{ $comment->comment }}</p>
                                                        <p class="chat-time mb-0"><i
                                                                class="bx bx-time-five align-middle me-1"></i>
                                                            {{ Carbon\Carbon::parse($comment->createdAt)->format('d/m/Y H:i A') }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </li>
                                            {{--  {{ dd($comment->createdBy, $employeeName) }}  --}}
                                            {{--  x- END OF COMMENT FROM OTHER USERS  --}}
                                        @elseif($comment->createdBy == $employeeName)
                                            {{--  USER COMMENT  --}}
                                            <li class=" right">
                                                <div class="conversation-list">
                                                    <div class="dropdown">
                                                        @if ($comment->createdBy == $employeeName)
                                                            <a class="dropdown-toggle" href="#" role="button"
                                                                data-bs-toggle="dropdown" aria-haspopup="true"
                                                                aria-expanded="false">
                                                                <i class="bx bx-dots-vertical-rounded"></i>
                                                            </a>
                                                            <div class="dropdown-menu">
                                                                <a class="dropdown-item" href="#"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#bs-edit-right-modal-lg-{{ $comment->id }}">Edit</a>
                                                                <a class="dropdown-item" href="#"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#bs-delete-right-modal-lg-{{ $comment->id }}">Delete</a>
                                                            </div>
                                                        @else
                                                            <div></div>
                                                        @endif
                                                    </div>

                                                    <div class="ctext-wrap">
                                                        <div class="conversation-name">{{ $comment->createdBy }}</div>
                                                        <p>
                                                            {{ $comment->comment }}
                                                        </p>

                                                        <p class="chat-time mb-0"><i
                                                                class="bx bx-time-five align-middle me-1"></i>
                                                            {{ Carbon\Carbon::parse($comment->createdAt)->format('d/m/Y H:i A') }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </li>
                                            {{--  x- END OF USER COMMENT   --}}
                                        @endif
                                    @empty
                                        <li>
                                            <div class="chat-day-title">
                                                <span class="title">No New Comments Yet</span>
                                            </div>
                                        </li>
                                    @endforelse
                                </ul>
                            </div>
                            <div class="p-3 chat-input-section">
                                <form action="{{ route('exception.comment.post', $exception->id) }}" method="POST">
                                    @csrf
                                    <div class="row">
                                        <div class="col">
                                            <div class="position-relative">
                                                <input type="text" name="comment" class="form-control chat-input"
                                                    placeholder="Enter Message...">
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <button type="submit"
                                                class="btn btn-primary btn-rounded chat-send w-md waves-effect waves-light"><span
                                                    class="d-none d-sm-inline-block me-2">Send</span> <i
                                                    class="mdi mdi-send"></i></button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- All modals moved outside the chat container -->
            @foreach ($sortedComments as $comment)
                @if ($comment->createdBy != $exception->auditorName)
                    {{--  EDITING COMMENT MODAL (LEFT) --}}
                    <div class="modal fade" id="bs-edit-left-modal-lg-{{ $comment->id }}" tabindex="-1"
                        role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="myLargeModalLabel">
                                        Edit Your Comment
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form action="{{ route('exception.comment.edit', $comment->id) }}"
                                        method="POST">
                                        @csrf
                                        <textarea class="form-control mb-3" rows="3" name="comment" placeholder="Enter comment......">{{ $comment->comment }}</textarea>
                                        <input type="hidden" name="exceptionTrackerId"
                                            value="{{ $exception->id }}">
                                        <div class="invalid-feedback">Please
                                            enter a comment.</div>
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary">
                                                Edit Comment
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{--  DELETING COMMENT MODAL (LEFT) --}}
                    <div class="modal fade" id="bs-delete-left-modal-lg-{{ $comment->id }}" tabindex="-1"
                        role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="myLargeModalLabel">
                                        Confirm Comment Deletion
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <h4 class="text-center mb-4">Are you sure you want to delete this comment?</h4>
                                    <p class="text-center">Deleting a <b>comment</b> means removing it from the
                                        <b>system entirely</b> and you cannot <b>recover</b> it again
                                    </p>
                                    <form action="{{ route('exception.comment.delete', $comment->id) }}"
                                        method="POST">
                                        @csrf
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-danger">Delete Comment</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    {{--  EDITING COMMENT MODAL (RIGHT) --}}
                    <div class="modal fade" id="bs-edit-right-modal-lg-{{ $comment->id }}" tabindex="-1"
                        role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="myLargeModalLabel">
                                        Edit Your Comment
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form action="{{ route('batch.delete', $batch->id) }}" method="POST">
                                        @csrf
                                        <textarea class="form-control" rows="3" name="exception" placeholder="Enter comment......">{{ $comment->comment }}</textarea>
                                        <div class="invalid-feedback">Please enter a comment.</div>
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-primary">Edit Comment</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{--  DELETING COMMENT MODAL (RIGHT) --}}
                    <div class="modal fade" id="bs-delete-right-modal-lg-{{ $comment->id }}" tabindex="-1"
                        role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="myLargeModalLabel">
                                        Confirm Comment Deletion
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <h4 class="text-center mb-4">Are you sure you want to delete this comment?</h4>
                                    <p class="text-center">Deleting a <b>comment</b> means removing it from the
                                        <b>system entirely</b> and you cannot <b>recover</b> it again
                                    </p>
                                    <form action="{{ route('batch.delete', $batch->id) }}" method="POST">
                                        @csrf
                                        <textarea class="form-control" rows="3" name="exception" placeholder="Enter comment......">{{ $comment->comment }}</textarea>
                                        <div class="invalid-feedback">Please enter a comment.</div>
                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-danger">Delete Comment</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach


            {{--  CLOSE EXCEPTION - AUDITOR  --}}
            @if ($exception->auditorId == $employeeId || in_array($employeeDepartmentId, [7, 8]))
                <div class="tab-pane" id="close-exception" role="tabpanel">

                    <div class="card">
                        <div class="card-body">
                            <h4>Close This Exception</h4>
                            <small>(By closing this exception (<strong>{{ $exception->exception }}</strong>) means
                                the exception has been resolved with respect to all /any other outstanding
                                issues)</small>

                            <div class="d-grid mt-2">
                                <button type="button" class="btn btn-dark" data-bs-toggle="modal"
                                    data-bs-target=".bs-close-exception-modal-lg-{{ $exception->id }}">Close
                                    Exception</button>
                            </div>
                        </div>
                    </div>

                    <!-- Modal for Delete Confirmation -->
                    <div class="modal fade bs-close-exception-modal-lg-{{ $exception->id }}" tabindex="-1"
                        role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="myLargeModalLabel">Confirm Exception Closure</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <h4 class="text-center mb-4">Are you sure you want to close
                                        this
                                        exception ({{ $exception->exception }})?</h4>
                                    <p class="text-center">Closing an <b>exception
                                            (<span class="required">{{ $exception->exception }}</span>)</b> means all
                                        related issues with regards
                                        to the exception has been resolved.
                                        Once closed, cannot be <strong>undone</strong>
                                    </p>
                                    <form action="{{ route('exception.close', $exception->id) }}" method="POST">
                                        @csrf

                                        <div class="d-grid">
                                            <button type="submit" class="btn btn-success">Yes,
                                                Close</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- end col -->


                </div>
            @else
                {{--  RECOMMEND RESOLUTION - USER  --}}

                <div class="tab-pane" id="recommend-resolution" role="tabpanel">

                    <form action="{{ route('exception.resolution', $exception->id) }}" method="POST"
                        enctype="multipart/form-data" autocomplete="on" class="needs-validation">
                        @csrf

                        <div class="card">
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label" for="project-status-input">Push to
                                        <strong>{{ $exception->auditorName }}</strong> your Auditor for
                                        Resolution</label>
                                    <select class="form-select" name="resolution" required>
                                        <option selected>Select.....</option>
                                        <option value="RESOLVED">Close Exception</option>
                                    </select>
                                    <div class="invalid-feedback">Please select exception status.</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid mt-3">
                            <button type="submit" class="btn btn-primary">Update Status</button>
                        </div>

                    </form>
                </div>


                <!-- end row -->
        </div>
        @endif


    </div>
    @push('scripts')
        <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <script>
            Dropzone.autoDiscover = false;
            var myDropzone = new Dropzone("#myId", {
                url: "{{ route('exception.file.upload', $exception->id) }}",
                paramName: "files[]",
                maxFilesize: 5,
                autoProcessQueue: false,
                addRemoveLinks: true,
                dictDefaultMessage: "Drop files here or click to upload",
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                init: function() {
                    var submitButton = document.querySelector("#upload-button");
                    var myDropzone = this;

                    submitButton.addEventListener("click", function() {
                        myDropzone.processQueue();
                    });

                    this.on("success", function(file, response) {
                        if (response.status === "success") {
                            Swal.fire({
                                icon: "success",
                                title: "Success",
                                text: response.message,
                                toast: true,
                                position: "top-end",
                                showConfirmButton: false,
                                timer: 3000
                            });

                            //refresh page manually
                            window.location.reload();


                            {{--  fetchExceptionFiles(); // Reload files dynamically  --}}
                            myDropzone.removeFile(file);
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: response.message,
                                toast: true,
                                position: "top-end",
                                showConfirmButton: false,
                                timer: 3000
                            });
                        }
                    });

                    this.on("error", function(file, response) {
                        Swal.fire({
                            icon: "error",
                            title: "Upload Failed",
                            text: response.message || "An error occurred",
                            toast: true,
                            position: "top-end",
                            showConfirmButton: false,
                            timer: 3000
                        });

                        file.previewElement.classList.add("dz-error");
                    });
                }
            });

            // Fetch and download files
            function downloadFile(fileId) {
                $.ajax({
                    url: "{{ route('exception.file.download', ':id') }}".replace(':id', fileId),
                    method: "GET",
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") // Include CSRF token
                    },
                    success: function(response) {
                        {{--  console.log("Download response:", response); // Debugging  --}}

                        if (response.status === "success") {
                            let link = document.createElement("a");
                            link.href = `data:application/octet-stream;base64,${response.fileData}`;
                            link.download = response.fileName;
                            document.body.appendChild(link);
                            link.click();
                            document.body.removeChild(link);
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Download Failed",
                                text: response.message,
                                toast: true,
                                position: "top-end",
                                showConfirmButton: false,
                                timer: 3000
                            });
                        }
                    },
                    error: function(xhr) {
                        {{--  console.error("Download Error:", xhr.responseText);  --}}
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "Failed to download file. Please try again.",
                            toast: true,
                            position: "top-end",
                            showConfirmButton: false,
                            timer: 3000
                        });
                    }
                });
            }





            //Delete exception files
            function deleteFile(fileId) {
                Swal.fire({
                    title: "Are you sure?",
                    text: "This file will be permanently deleted!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#d33",
                    cancelButtonColor: "#3085d6",
                    confirmButtonText: "Yes, delete it!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('exception.file.delete', ':id') }}".replace(':id', fileId),
                            method: "DELETE", // Changed to DELETE
                            headers: {
                                "X-CSRF-TOKEN": "{{ csrf_token() }}" // CSRF token added
                            },
                            success: function(response) {
                                {{--  console.log("Delete Success:", response); // Debugging log  --}}

                                if (response.status === "success") {
                                    Swal.fire({
                                        icon: "success",
                                        title: "Deleted!",
                                        text: response.message,
                                        toast: true,
                                        position: "top-end",
                                        showConfirmButton: false,
                                        timer: 3000
                                    });

                                    // Remove file from UI
                                    document.querySelector(`#file-${fileId}`).remove();
                                } else {
                                    Swal.fire({
                                        icon: "error",
                                        title: "Error",
                                        text: response.message,
                                        toast: true,
                                        position: "top-end",
                                        showConfirmButton: false,
                                        timer: 3000
                                    });
                                }
                            },
                            error: function(xhr) {
                                {{--  console.error("Delete Error:", xhr.responseText); // Log error for debugging  --}}

                                Swal.fire({
                                    icon: "error",
                                    title: "Error",
                                    text: "Failed to delete file. Please try again.",
                                    toast: true,
                                    position: "top-end",
                                    showConfirmButton: false,
                                    timer: 3000
                                });
                            }
                        });
                    }
                });
            }
        </script>



        <script>
            // Save the active tab state to local storage
            document.querySelectorAll('a[data-bs-toggle="tab"]').forEach(function(tab) {
                tab.addEventListener('shown.bs.tab', function(e) {
                    localStorage.setItem('activeTab', e.target.getAttribute('href'));
                });
            });

            // Restore the active tab state from local storage
            document.addEventListener('DOMContentLoaded', function() {
                var activeTab = localStorage.getItem('activeTab');
                if (activeTab) {
                    var tabElement = document.querySelector('a[href="' + activeTab + '"]');
                    if (tabElement) {
                        var tab = new bootstrap.Tab(tabElement);
                        tab.show();
                    }
                }
            });
        </script>
    @endpush
</x-base-layout>
