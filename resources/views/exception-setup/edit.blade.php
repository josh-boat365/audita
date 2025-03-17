<x-base-layout>

    <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css" />
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18"> <a href="{{ route('exception.list') }}">
                            {{ $exception->exception }}</a> > Update Exception
                    </h4>
                </div>
            </div>
        </div>
        <!-- end page title -->


        <div class="mt-4 mb-4" style="background-color: gray; height: 1px;"></div>

        <ul class="nav nav-tabs nav-tabs-custom nav-justified" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#exception-creation" role="tab">
                    <span class="d-block d-sm-none"><i class="fas fa-file-alt"></i></span>
                    <span class="d-none d-sm-block">Exception Update</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#file-attachments" role="tab">
                    <span class="d-block d-sm-none"><i class="fas fa-paperclip"></i></span>
                    <span class="d-none d-sm-block">Attach Files</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#chats-comments" role="tab">
                    <span class="d-block d-sm-none"><i class="fas fa-paperclip"></i></span>
                    <span class="d-none d-sm-block">Chats & Comments</span>
                </a>
            </li>

        </ul>

        <div class="tab-content p-3 text-muted">
            <div class="tab-pane active" id="exception-creation" role="tabpanel">

                <form action="{{ route('exception.update', $exception->id) }}" method="POST" enctype="multipart/form-data" autocomplete="on"
                    class="needs-validation">
                    @csrf
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-body">

                                    {{--  Project Image   --}}
                                    <div class="mb-3">
                                        <label class="form-label">Exception</label>
                                        <textarea class="form-control" rows="3" name="exception" placeholder="Enter exception details......" required>
                                            {{ $exception->exception }}
                                        </textarea>
                                        <div class="invalid-feedback">Please enter an exception.</div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Root Cause</label>
                                        <textarea class="form-control" rows="3" name="rootCause" placeholder="Enter root cause details......" required>{{ $exception->rootCause }}</textarea>
                                        <div class="invalid-feedback">Please enter the root cause.</div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Batch</label>
                                        <select class="form-select select2" name="exceptionBatchId" required>
                                            <option selected>Select.....</option>
                                            @foreach ($batches as $batch)
                                                <option value="{{ $batch->id }}" @selected($batch->id === $exception->exceptionBatchId)>
                                                    {{ $batch->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Unit/Dept</label>
                                        <select class="form-select select2" name="departmentId" required>
                                            <option>Select Unit/Department</option>
                                            @foreach ($departments as $department)
                                                <option value="{{ $department->id }}" @selected($department->id === $exception->departmentId)>
                                                    {{ $department->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    {{ dd($exception->occurrenceDate) }}
                                    <div class="mb-3">
                                        <label class="form-label">Occurrence Date</label>
                                        <input type="text" class="form-control" placeholder="Select occurrence date"
                                            name="occurrenceDate"
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
                                        <select class="form-select" name="status" required>
                                            <option selected>Select.....</option>
                                            <option value="PENDING" @selected($exception->status === 'PENDING')>Pending</option>
                                            <option value="RESOLVED" @selected($exception->status === 'RESOLVED')>Resolved</option>
                                        </select>
                                        <div class="invalid-feedback">Please select exception status.</div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="project-visibility-input">Risk Rate</label>
                                        <select class="form-select select2" name="riskRateId" required>
                                            <option selected>Select.....</option>
                                            @foreach ($riskRates as $riskRate)
                                                <option value="{{ $riskRate->id }}" @selected($riskRate->id === $exception->riskRateId)>
                                                    {{ $riskRate->name }}</option>
                                            @endforeach

                                        </select>
                                    </div>

                                    <div>
                                        <label class="form-label" for="project-visibility-input">Process
                                            Type/Scope</label>
                                        <select class="form-select select2" name="processTypeId" required>
                                            <option selected>Select.....</option>
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
                                        <input type="text" class="form-control" placeholder="Select due date"
                                            name="proposeResolutionDate"
                                            value="{{ $exception->proposeResolutionDate == null ? '' : Carbon\Carbon::parse($exception->proposeResolutionDate)->format('d/m/Y') }}"
                                            data-date-format="d/m/Y" data-provide="datepicker"
                                            data-date-autoclose="true" />
                                        <div class="invalid-feedback">Please select proposed resolution date.</div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Resolution Date</label>
                                        <input type="text" class="form-control"
                                            placeholder="Select resolution date" name="resolutionDate"
                                            value="{{ $exception->resolutionDate == null ? '' : Carbon\Carbon::parse($exception->resolutionDate)->format('d/m/Y') }}"
                                            data-date-format="d/m/Y" data-provide="datepicker"
                                            data-date-autoclose="true" />
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

            <div class="tab-pane" id="file-attachments" role="tabpanel">
                <form action="" method="POST" enctype="multipart/form-data" autocomplete="on">
                    @csrf
                    <div>
                        <label class="form-label">Attach Files</label>
                        <div class="dropzone" id="myId">
                            <div class="dz-message needsclick">
                                <div class="mb-3">
                                    <i class="display-4 text-muted bx bxs-cloud-upload"></i>
                                </div>
                                <h4>Drop files here or click to upload.</h4>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="text-end mb-4">
                            <button type="submit" class="btn btn-primary">Upload File</button>
                        </div>
                    </div>
                </form>

                <div class="card">
                    <div class="card-body">
                        <h4>Files</h4>
                        <div class="mt-4 mb-4" style="background-color: gray; height: 1px;"></div>

                    </div>
                </div>



            </div>

            <div class="tab-pane" id="chats-comments" role="tabpanel">
                <div class="w-100 user-chat">
                    <div class="card">
                        <div class="p-4 border-bottom ">
                            <div class="row">
                                <div class="col-md-4 col-9">
                                    <h5 class="font-size-15 mb-1">Steven Franklin</h5>
                                    <p class="text-muted mb-0"><i
                                            class="mdi mdi-circle text-success align-middle me-1"></i> Active now</p>
                                </div>
                                <div class="col-md-8 col-3">
                                    <ul class="list-inline user-chat-nav text-end mb-0">
                                        <li class="list-inline-item d-none d-sm-inline-block">
                                            <div class="dropdown">
                                                <button class="btn nav-btn dropdown-toggle" type="button"
                                                    data-bs-toggle="dropdown" aria-haspopup="true"
                                                    aria-expanded="false">
                                                    <i class="bx bx-search-alt-2"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-end dropdown-menu-md">
                                                    <form class="p-3">
                                                        <div class="form-group m-0">
                                                            <div class="input-group">
                                                                <input type="text" class="form-control"
                                                                    placeholder="Search ..."
                                                                    aria-label="Recipient's username">

                                                                <button class="btn btn-primary" type="submit"><i
                                                                        class="mdi mdi-magnify"></i></button>

                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="list-inline-item  d-none d-sm-inline-block">
                                            <div class="dropdown">
                                                <button class="btn nav-btn dropdown-toggle" type="button"
                                                    data-bs-toggle="dropdown" aria-haspopup="true"
                                                    aria-expanded="false">
                                                    <i class="bx bx-cog"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a class="dropdown-item" href="#">View Profile</a>
                                                    <a class="dropdown-item" href="#">Clear chat</a>
                                                    <a class="dropdown-item" href="#">Muted</a>
                                                    <a class="dropdown-item" href="#">Delete</a>
                                                </div>
                                            </div>
                                        </li>

                                        <li class="list-inline-item">
                                            <div class="dropdown">
                                                <button class="btn nav-btn dropdown-toggle" type="button"
                                                    data-bs-toggle="dropdown" aria-haspopup="true"
                                                    aria-expanded="false">
                                                    <i class="bx bx-dots-horizontal-rounded"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a class="dropdown-item" href="#">Action</a>
                                                    <a class="dropdown-item" href="#">Another action</a>
                                                    <a class="dropdown-item" href="#">Something else</a>
                                                </div>
                                            </div>
                                        </li>

                                    </ul>
                                </div>
                            </div>
                        </div>


                        <div>
                            <div class="chat-conversation p-3">
                                <ul class="list-unstyled mb-0" data-simplebar style="max-height: 486px;">
                                    <li>
                                        <div class="chat-day-title">
                                            <span class="title">Today</span>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="conversation-list">
                                            <div class="dropdown">

                                                <a class="dropdown-toggle" href="#" role="button"
                                                    data-bs-toggle="dropdown" aria-haspopup="true"
                                                    aria-expanded="false">
                                                    <i class="bx bx-dots-vertical-rounded"></i>
                                                </a>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="#">Copy</a>
                                                    <a class="dropdown-item" href="#">Save</a>
                                                    <a class="dropdown-item" href="#">Forward</a>
                                                    <a class="dropdown-item" href="#">Delete</a>
                                                </div>
                                            </div>
                                            <div class="ctext-wrap">
                                                <div class="conversation-name">Steven Franklin</div>
                                                <p>
                                                    Hello!
                                                </p>
                                                <p class="chat-time mb-0"><i
                                                        class="bx bx-time-five align-middle me-1"></i> 10:00</p>
                                            </div>

                                        </div>
                                    </li>

                                    <li class="right">
                                        <div class="conversation-list">
                                            <div class="dropdown">

                                                <a class="dropdown-toggle" href="#" role="button"
                                                    data-bs-toggle="dropdown" aria-haspopup="true"
                                                    aria-expanded="false">
                                                    <i class="bx bx-dots-vertical-rounded"></i>
                                                </a>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="#">Copy</a>
                                                    <a class="dropdown-item" href="#">Save</a>
                                                    <a class="dropdown-item" href="#">Forward</a>
                                                    <a class="dropdown-item" href="#">Delete</a>
                                                </div>
                                            </div>
                                            <div class="ctext-wrap">
                                                <div class="conversation-name">Henry Wells</div>
                                                <p>
                                                    Hi, How are you? What about our next meeting?
                                                </p>

                                                <p class="chat-time mb-0"><i
                                                        class="bx bx-time-five align-middle me-1"></i> 10:02</p>
                                            </div>
                                        </div>
                                    </li>

                                    <li>
                                        <div class="conversation-list">
                                            <div class="dropdown">

                                                <a class="dropdown-toggle" href="#" role="button"
                                                    data-bs-toggle="dropdown" aria-haspopup="true"
                                                    aria-expanded="false">
                                                    <i class="bx bx-dots-vertical-rounded"></i>
                                                </a>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="#">Copy</a>
                                                    <a class="dropdown-item" href="#">Save</a>
                                                    <a class="dropdown-item" href="#">Forward</a>
                                                    <a class="dropdown-item" href="#">Delete</a>
                                                </div>
                                            </div>
                                            <div class="ctext-wrap">
                                                <div class="conversation-name">Steven Franklin</div>
                                                <p>
                                                    Yeah everything is fine
                                                </p>

                                                <p class="chat-time mb-0"><i
                                                        class="bx bx-time-five align-middle me-1"></i> 10:06</p>
                                            </div>

                                        </div>
                                    </li>

                                    <li class="last-chat">
                                        <div class="conversation-list">
                                            <div class="dropdown">

                                                <a class="dropdown-toggle" href="#" role="button"
                                                    data-bs-toggle="dropdown" aria-haspopup="true"
                                                    aria-expanded="false">
                                                    <i class="bx bx-dots-vertical-rounded"></i>
                                                </a>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="#">Copy</a>
                                                    <a class="dropdown-item" href="#">Save</a>
                                                    <a class="dropdown-item" href="#">Forward</a>
                                                    <a class="dropdown-item" href="#">Delete</a>
                                                </div>
                                            </div>
                                            <div class="ctext-wrap">
                                                <div class="conversation-name">Steven Franklin</div>
                                                <p>& Next meeting tomorrow 10.00AM</p>
                                                <p class="chat-time mb-0"><i
                                                        class="bx bx-time-five align-middle me-1"></i> 10:06</p>
                                            </div>

                                        </div>
                                    </li>

                                    <li class=" right">
                                        <div class="conversation-list">
                                            <div class="dropdown">

                                                <a class="dropdown-toggle" href="#" role="button"
                                                    data-bs-toggle="dropdown" aria-haspopup="true"
                                                    aria-expanded="false">
                                                    <i class="bx bx-dots-vertical-rounded"></i>
                                                </a>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="#">Copy</a>
                                                    <a class="dropdown-item" href="#">Save</a>
                                                    <a class="dropdown-item" href="#">Forward</a>
                                                    <a class="dropdown-item" href="#">Delete</a>
                                                </div>
                                            </div>
                                            <div class="ctext-wrap">
                                                <div class="conversation-name">Henry Wells</div>
                                                <p>
                                                    Wow thats great
                                                </p>

                                                <p class="chat-time mb-0"><i
                                                        class="bx bx-time-five align-middle me-1"></i> 10:07</p>
                                            </div>
                                        </div>
                                    </li>


                                </ul>
                            </div>
                            <div class="p-3 chat-input-section">
                                <div class="row">
                                    <div class="col">
                                        <div class="position-relative">
                                            <input type="text" class="form-control chat-input"
                                                placeholder="Enter Message...">
                                            <div class="chat-input-links" id="tooltip-container">
                                                <ul class="list-inline mb-0">
                                                    <li class="list-inline-item"><a href="javascript: void(0);"
                                                            title="Emoji"><i
                                                                class="mdi mdi-emoticon-happy-outline"></i></a></li>
                                                    <li class="list-inline-item"><a href="javascript: void(0);"
                                                            title="Images"><i
                                                                class="mdi mdi-file-image-outline"></i></a></li>
                                                    <li class="list-inline-item"><a href="javascript: void(0);"
                                                            title="Add Files"><i
                                                                class="mdi mdi-file-document-outline"></i></a></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <button type="submit"
                                            class="btn btn-primary btn-rounded chat-send w-md waves-effect waves-light"><span
                                                class="d-none d-sm-inline-block me-2">Send</span> <i
                                                class="mdi mdi-send"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>


        </div>
        <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
        <script>
            Dropzone.autoDiscover = false;
            var myDropzone = new Dropzone("#myId", {
                url: "/file/post", // Set the url for your upload script
                paramName: "file", // The name that will be used to transfer the file
                maxFilesize: 5, // MB
                addRemoveLinks: true,
                dictDefaultMessage: "Drop files here or click to upload",
                init: function() {
                    this.on("success", function(file, response) {
                        console.log("File uploaded successfully");
                    });
                    this.on("error", function(file, response) {
                        console.log("File upload failed");
                    });
                }
            });
        </script>
</x-base-layout>
