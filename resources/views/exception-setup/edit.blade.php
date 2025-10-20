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
                                List of Exceptions </a> > Update Exception > <a href="#">exception</a>
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
                {{--  <li class="nav-item">
                    <a class="nav-link border border-3" data-bs-toggle="tab" href="#close-exception" role="tab">
                        <span class="d-block d-sm-none"><i class="fas fa-paperclip"></i></span>
                        <span class="d-none d-sm-block">Close Exception</span>
                    </a>
                </li>  --}}
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
                @if($employeeDepartmentId === 8)
                    <form action="{{ route('exception.update', $exception->id) }}" method="POST"
                        enctype="multipart/form-data" autocomplete="on" class="needs-validation">
                        @csrf
                        <input type="hidden" name="requestType" value="">
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label class="form-label">Exception Title <span
                                                    class="required">*</span></label>
                                            <textarea class="form-control" rows="3" name="exceptionTitle" placeholder="Enter exception title......" required>{{ $exception->exceptionTitle ?? '-----------' }}</textarea>
                                            <div class="invalid-feedback">Please enter exception title.</div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Exception Description <span
                                                    class="required">*</span></label>
                                            <textarea @disabled(!$canEdit) class="form-control" rows="3" id="exception" name="exception"
                                                placeholder="Enter exception description......" required>{{ $exception->exception ?? '-----------' }}</textarea>
                                            <div class="invalid-feedback">Please enter an exception.</div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Root Cause</label>
                                            <textarea @disabled(!$canEdit) class="form-control" rows="3" name="rootCause"
                                                placeholder="Enter root cause details......">{{ $exception->rootCause ?? '-----------' }}</textarea>
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
                                            <label class="form-label">Group<span class="required">*</span></label>
                                            <select class="form-select select2" name="activityGroupId" required>
                                                <option>Select.....</option>
                                                @foreach ($groups as $group)
                                                    <option value="{{ $group->id }}" @selected($group->id === $exception->activityGroupId)>
                                                        {{ $group->name }}
                                                    </option>
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
                                            <input @disabled(!$canEdit)type="date" class="form-control"
                                                name="occurrenceDate"
                                                value="{{ $exception->occurrenceDate ? \Carbon\Carbon::parse($exception->occurrenceDate)->format('Y-m-d') : '' }}"
                                                required />
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
                                                <option value="NOT-RESOLVED" @selected($exception->status === 'RESOLVED')>Not Resolved</option>
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
                                                <option>Select.....</option>
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
                                            <input @disabled(!$canEdit)type="date" class="form-control"
                                                name="proposeResolutionDate"
                                                value="{{ $exception->proposeResolutionDate ? \Carbon\Carbon::parse($exception->proposeResolutionDate)->format('Y-m-d') : '' }}" />
                                            <div class="invalid-feedback">Please select proposed resolution date.</div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Resolution Date</label>
                                            <small>(optional)</small>
                                            <input @disabled(!$canEdit) type="date" class="form-control"
                                                name="resolutionDate"
                                                value="{{ $exception->resolutionDate ? \Carbon\Carbon::parse($exception->resolutionDate)->format('Y-m-d') : '' }}" />
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
                @else
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card border border-primary-subtle">
                            <div class="card-body">
                                <h5 class="card-title mb-4">Exception Details</h5>
                                <!-- Exception Title -->
                                <div class="mb-4">
                                    <h6 class="text-muted mb-2">Exception Title</h6>
                                    <p class="mb-0">{{ $exception->exceptionTitle ?? 'N/A' }}</p>
                                </div>

                                <!-- Exception Description -->
                                <div class="mb-4">
                                    <h6 class="text-muted mb-2">Exception Description</h6>
                                    <p class="mb-0">{{ $exception->exception ?? 'N/A' }}</p>
                                </div>

                                <!-- Root Cause -->
                                <div class="mb-4">
                                    <h6 class="text-muted mb-2">Root Cause</h6>
                                    <p class="mb-0">{{ $exception->rootCause ?: 'Not specified' }}
                                    </p>
                                </div>

                                <!-- Branch/Auditee Response -->
                                <div class="mb-4">
                                    <h6 class="text-muted mb-2">Branch/Auditee Response</h6>
                                    <p class="mb-0">
                                        {{ $exception->statusComment ?: 'No response has been provided' }}</p>
                                </div>

                                <!-- Department and Occurrence Date Row -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-4">
                                            <h6 class="text-muted mb-2">Unit/Department</h6>
                                            <p class="mb-0">{{ $exception->department ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-4">
                                            <h6 class="text-muted mb-2">Occurrence Date</h6>
                                            <p class="mb-0">
                                                {{ $exception->occurrenceDate ? \Carbon\Carbon::parse($exception->occurrenceDate)->format('M d, Y') : 'Not specified' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <!-- Status Card -->
                        <div class="card">
                            <div class="card-body border border-primary-subtle">
                                <h5 class="card-title mb-4">Exception Statuses</h5>

                                <!-- Status -->
                                <div class="mb-3">
                                    <h6 class="text-muted mb-2">Status</h6>
                                    <span class="badge
                                                        @if ($exception->status === 'APPROVED') bg-primary
                                                        @elseif($exception->status === 'RESOLVED') bg-success
                                                        @elseif($exception->status === 'NOT-RESOLVED') bg-warning
                                                        @else bg-secondary @endif fs-6">
                                        {{ $exception->status ?: 'Pending' }}
                                    </span>
                                </div>

                                <!-- Risk Rate -->
                                <div class="mb-3">
                                    <h6 class="text-muted mb-2">Risk Rate</h6>
                                    <p class="mb-0">
                                        @if ($exception->riskRate)
                                        {{ $exception->riskRate }}
                                        @else
                                        Not specified
                                        @endif
                                    </p>
                                </div>

                                <!-- Batch -->
                                <div class="mb-3">
                                    <h6 class="text-muted mb-2">Batch</h6>
                                    <p class="mb-0">
                                        @if ($exception->exceptionBatch)
                                        {{ $exception->exceptionBatch ?? 'Not specified' }}
                                        @else
                                        Not specified
                                        @endif
                                    </p>
                                </div>

                                <!-- Process Type -->
                                <div class="mb-3">
                                    <h6 class="text-muted mb-2">Process Type/Scope</h6>
                                    <p class="mb-0">
                                        @if ($exception->processType)
                                        {{ $exception->processType }}
                                        @else
                                        Not specified
                                        @endif
                                    </p>
                                </div>

                                <!-- Sub Process Type -->
                                <div class="mb-3">
                                    <h6 class="text-muted mb-2">Sub Process Type/Scope</h6>
                                    <p class="mb-0">
                                        @if ($exception->subProcessType)
                                        {{ $exception->subProcessType }}
                                        @else
                                        Not specified
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Dates Card -->
                        <div class="card">
                            <div class="card-body border border-primary-subtle">
                                <h6 class="card-title mb-3">Important Dates</h6>

                                <!-- Proposed Resolution Date -->
                                <div class="mb-3">
                                    <h6 class="text-muted mb-2">Proposed Resolution Date</h6>
                                    <p class="mb-0">
                                        @if ($exception->proposeResolutionDate)
                                        {{ \Carbon\Carbon::parse($exception->proposeResolutionDate)->format('M d, Y') }}
                                        @else
                                        <span class="text-muted">Not set</span>
                                        @endif
                                    </p>
                                </div>

                                <!-- Resolution Date -->
                                <div class="mb-3">
                                    <h6 class="text-muted mb-2">Resolution Date</h6>
                                    <p class="mb-0">
                                        @if ($exception->resolutionDate)
                                        {{ \Carbon\Carbon::parse($exception->resolutionDate)->format('M d, Y') }}
                                        @else
                                        <span class="text-muted">Not resolved</span>
                                        @endif
                                    </p>
                                </div>

                                @if ($exception->createdAt)
                                <!-- Created Date -->
                                <div class="mb-0">
                                    <h6 class="text-muted mb-2">Created</h6>
                                    <p class="mb-0">
                                        {{ \Carbon\Carbon::parse($exception->createdAt)->format('M d, Y \a\t g:i A') }}
                                    </p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            {{-- FILE UPLOAD --}}
            <div class="tab-pane" id="file-attachments" role="tabpanel">

                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">
                            <i class="bx bx-file me-2"></i>Attached Files
                            <span class="badge bg-secondary ms-2">
                                {{ count($exception->fileAttached ?? []) }}
                            </span>
                        </h6>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <form id="file-upload-form-{{ $exception->id }}"
                                action="{{ route('exception.file.upload', $exception->id) }}" method="POST"
                                enctype="multipart/form-data">
                                @csrf
                                <label class="form-label">Attach Files</label>

                                <!-- Hidden file input -->
                                <input type="file" id="file-input-{{ $exception->id }}" name="files[]"
                                    multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.png,.jpg,.jpeg,.gif,.txt,.zip,.rar"
                                    style="display: none;">

                                <!-- Dropzone area -->
                                <div id="file-upload-dropzone-{{ $exception->id }}" class="dropzone"
                                    style="border: 2px dashed #0087F7; border-radius: 5px; background: white; cursor: pointer; min-height: 150px;"
                                    onclick="document.getElementById('file-input-{{ $exception->id }}').click();">
                                    <div class="dz-message needsclick" style="margin: 2em 0; text-align: center;">
                                        <div class="mb-3">
                                            <i class="display-4 text-muted bx bxs-cloud-upload"></i>
                                        </div>
                                        <h4 style="margin-bottom: 10px;">Drop files here or click to upload.</h4>
                                        <span class="text-muted">Maximum file size: 5MB</span>
                                    </div>
                                    <!-- File preview area -->
                                    <div id="file-preview-{{ $exception->id }}" class="mt-3"></div>
                                </div>
                                <div class="mt-4 text-end">
                                    <!-- Made button ID unique and initially hidden -->
                                    <button type="button" id="upload-button-{{ $exception->id }}"
                                        class="btn btn-primary" style="display: none;">Upload
                                        File</button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>

                {{-- FILE PREVIEWS --}}

                <div class="card">
                    <div class="card-body">
                        <h4>Files</h4>
                        <div class="mt-4 mb-4" style="background-color: gray; height: 1px;"></div>
                        <div class="row">
                            <!-- Fixed: Use consistent variable name -->
                            @forelse ($exception->fileAttached ?? [] as $file)
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
                                <div class="text-center text-muted py-4">
                                    <i class="bx bx-file fs-1 text-muted"></i>
                                    <p class="mb-0">No files attached yet</p>
                                    <small>Upload files to share with your team</small>
                                </div>
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
                                    <form action="{{ route('exception.comment.edit', $comment->id) }}" method="POST">
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
                                    <form action="{{ route('exception.comment.delete', $comment->id) }} method="POST">
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
            @if ($exception->auditorId == $employeeId || in_array($employeeDepartmentId, [8]))
                {{--  <div class="tab-pane" id="close-exception" role="tabpanel">

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


                </div>  --}}
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


    @push('styles')
    <style>
        .dropzone {
            border: 2px dashed #0087F7 !important;
            border-radius: 5px !important;
            background: white !important;
            cursor: pointer !important;
            min-height: 150px !important;
            transition: all 0.3s ease;
        }

        .dropzone:hover {
            border-color: #0056b3 !important;
            background: #f8f9fa !important;
        }

        .dropzone.dz-drag-hover {
            border-color: #28a745 !important;
            background: #d4edda !important;
        }

        .dropzone .dz-message {
            margin: 2em 0 !important;
            text-align: center !important;
        }

        .dropzone .dz-message .needsclick {
            cursor: pointer !important;
        }

        .dropzone .dz-preview {
            margin: 10px;
        }

        .dropzone .dz-preview .dz-remove {
            cursor: pointer;
            color: #dc3545;
        }

        .dropzone .dz-preview .dz-remove:hover {
            text-decoration: underline;
        }
    </style>
@endpush

    @push('scripts')
        <script>
        // Initialize dropzone when modal is shown
        {{--  $('#fileAttachmentsModal-{{ $exception->id }}').on('shown.bs.modal', function () {
            initializeFileUpload{{ $exception->id }}();
        });  --}}

        {{--  function initializeFileUpload{{ $exception->id }}() {  --}}
            var fileInput = document.getElementById('file-input-{{ $exception->id }}');
            var dropzoneArea = document.getElementById('file-upload-dropzone-{{ $exception->id }}');
            var previewArea = document.getElementById('file-preview-{{ $exception->id }}');
            var uploadButton = document.getElementById('upload-button-{{ $exception->id }}');
            var selectedFiles = [];

            console.log("File upload initialized for exception {{ $exception->id }}");

            // Handle file input change
            fileInput.addEventListener('change', function(e) {
                var files = Array.from(e.target.files);
                console.log("Files selected:", files.length);

                if (files.length > 0) {
                    selectedFiles = files;
                    displayFilePreview(files);
                }
            });

            // Handle drag and drop
            dropzoneArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                dropzoneArea.style.borderColor = '#28a745';
                dropzoneArea.style.background = '#d4edda';
            });

            dropzoneArea.addEventListener('dragleave', function(e) {
                e.preventDefault();
                dropzoneArea.style.borderColor = '#0087F7';
                dropzoneArea.style.background = 'white';
            });

            dropzoneArea.addEventListener('drop', function(e) {
                e.preventDefault();
                dropzoneArea.style.borderColor = '#0087F7';
                dropzoneArea.style.background = 'white';

                var files = Array.from(e.dataTransfer.files);
                console.log("Files dropped:", files.length);

                if (files.length > 0) {
                    selectedFiles = files;
                    displayFilePreview(files);
                }
            });

            // Display file preview
            function displayFilePreview(files) {
                previewArea.innerHTML = '';

                files.forEach(function(file, index) {
                    var fileDiv = document.createElement('div');
                    fileDiv.className = 'file-preview-item d-flex justify-content-between align-items-center p-2 mb-2 border rounded';
                    fileDiv.innerHTML = `
                        <div class="d-flex align-items-center">
                            <i class="bx bx-file me-2"></i>
                            <span class="file-name">${file.name}</span>
                            <span class="text-muted ms-2">(${formatFileSize(file.size)})</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFile${{{ $exception->id }}}(${index})">
                            <i class="bx bx-x"></i>
                        </button>
                    `;
                    previewArea.appendChild(fileDiv);
                });

                // Show upload button
                uploadButton.style.display = 'inline-block';
            }

            // Remove file from selection
            window['removeFile{{ $exception->id }}'] = function(index) {
                selectedFiles.splice(index, 1);
                if (selectedFiles.length > 0) {
                    displayFilePreview(selectedFiles);
                } else {
                    previewArea.innerHTML = '';
                    uploadButton.style.display = 'none';
                }
            };

            // Handle upload button click
            uploadButton.addEventListener('click', function(e) {
                e.preventDefault();
                console.log("Upload button clicked, files to upload:", selectedFiles.length);

                if (selectedFiles.length === 0) {
                    Swal.fire({
                        icon: "warning",
                        title: "No Files",
                        text: "Please select files to upload first",
                        toast: true,
                        position: "top-end",
                        showConfirmButton: false,
                        timer: 3000
                    });
                    return;
                }

                uploadFiles(selectedFiles);
            });

            // Upload files function
            function uploadFiles(files) {
                var formData = new FormData();

                // Add files to form data
                files.forEach(function(file) {
                    formData.append('files[]', file);
                });

                // Add CSRF token
                formData.append('_token', '{{ csrf_token() }}');

                // Show loading state
                uploadButton.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Uploading...';
                uploadButton.disabled = true;

                $.ajax({
                    url: "{{ route('exception.file.upload', $exception->id) }}",
                    method: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        console.log("Upload success:", response);

                        // Reset button
                        uploadButton.innerHTML = 'Upload File';
                        uploadButton.disabled = false;
                        uploadButton.style.display = 'none';

                        if (response.status === "success") {
                            Swal.fire({
                                icon: "success",
                                title: "Success",
                                text: response.message || "Files uploaded successfully",
                                toast: true,
                                position: "top-end",
                                showConfirmButton: false,
                                timer: 3000
                            });

                            // Clear selections and refresh
                            selectedFiles = [];
                            previewArea.innerHTML = '';
                            fileInput.value = '';

                            // Refresh the page to show new files
                            setTimeout(function() {
                                window.location.reload();
                            }, 1500);
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Error",
                                text: response.message || "Upload failed",
                                toast: true,
                                position: "top-end",
                                showConfirmButton: false,
                                timer: 3000
                            });
                        }
                    },
                    error: function(xhr) {
                        console.log("Upload error:", xhr);

                        // Reset button
                        uploadButton.innerHTML = 'Upload File';
                        uploadButton.disabled = false;

                        var errorMessage = "An error occurred during upload";

                        try {
                            var response = JSON.parse(xhr.responseText);
                            if (response.message) {
                                errorMessage = response.message;
                            } else if (response.errors) {
                                errorMessage = Object.values(response.errors).join(', ');
                            }
                        } catch (e) {
                            if (xhr.responseText) {
                                errorMessage = xhr.responseText;
                            }
                        }

                        Swal.fire({
                            icon: "error",
                            title: "Upload Failed",
                            text: errorMessage,
                            toast: true,
                            position: "top-end",
                            showConfirmButton: false,
                            timer: 5000
                        });
                    }
                });
            }

            // Format file size helper
            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }
        {{--  }  --}}

        // Download file function
        function downloadFile(fileId) {
            $.ajax({
                url: "{{ route('exception.file.download', ':id') }}".replace(':id', fileId),
                method: "GET",
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                },
                success: function(response) {
                    if (response.status === "success") {
                        // Create download link
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

        // Delete file function
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
                        method: "DELETE",
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}"
                        },
                        success: function(response) {
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

                                // Remove file from UI with animation
                                $(`#file-${fileId}`).fadeOut(300, function() {
                                    $(this).remove();
                                });
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
    @endpush

    @push('scripts')
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
