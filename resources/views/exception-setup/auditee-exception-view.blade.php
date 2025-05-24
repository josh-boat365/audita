<x-base-layout>
    <div class="container-fluid px-1">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18"><a href="{{ route('auditee.exception.list') }}">Exceptions</a> >
                        Micheal Asenso > Sweduru > <a href="#">Operations</a></h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center g-2">
                            <div class="col-md-3">
                                <label class="form-label">Batch</label>
                                <select class="form-select select2" id="batchFilter">
                                    <option>Select.....</option>
                                    {{--  @foreach ($batches as $batch)  --}}
                                    {{--  <option value="{{ $batch->id }}">{{ $batch->name }}</option>  --}}
                                    {{--  @endforeach  --}}
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Category</label>
                                <select class="form-select select2" id="categoryFilter">
                                    <option>Select.....</option>
                                    {{--  @foreach ($categories as $category)  --}}
                                    {{--  <option value="{{ $category->id }}">{{ $category->name }}</option>  --}}
                                    {{--  @endforeach  --}}
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Department</label>
                                <select class="form-select select2" id="departmentFilter">
                                    <option>Select.....</option>
                                    {{--  @foreach ($departments as $department)  --}}
                                    {{--  <option value="{{ $department->id }}">{{ $department->name }}</option>  --}}
                                    {{--  @endforeach  --}}
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Occurrence Date</label>
                                <input type="date" class="form-control" id="occurrenceDateFilter">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 mb-4" style="background-color: gray; height: 1px;"></div>

        <form id="bulkExceptionForm" action="#" method="POST">
            @csrf
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0" id="exceptionsTable">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Exception Title</th>
                            <th scope="col">Exception Description</th>
                            <th scope="col">Sub Category</th>
                            <th scope="col">Exception Response</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <td>1</td>
                        <td>
                            <textarea disabled class="form-control editable-textarea" rows="3" name="exceptions"
                                placeholder="Enter exception title"></textarea>
                        </td>
                        <td>
                            <textarea disabled class="form-control editable-textarea" rows="3" name="exceptions"
                                placeholder="Enter exception description"></textarea>
                        </td>
                        <td>
                            <select disabled class="form-select" name="exceptions[${rowCount}][sub_category_id]">
                                <option value="">Select...</option>
                                {{--  @foreach ($subCategories as $subCat)  --}}
                                {{--  <option value="{{ $subCat->id }}">{{ $subCat->name }}</option>  --}}
                                {{--  @endforeach  --}}
                            </select>
                        </td>
                        <td>
                            <textarea class="form-control editable-textarea" rows="3" name="exceptions[${rowCount}][description]"
                                placeholder="Enter exception description"></textarea>
                        </td>
                        <td>
                            <div class="d-flex gap-3">
                                <a href="" data-bs-toggle="modal" data-bs-target=".bs-comments-modal">
                                    <span class="badge round bg-primary font-size-13"><i
                                            class="bx bx-message-dots"></i></span>
                                </a>
                                <a href="" data-bs-toggle="modal" data-bs-target=".bs-file-attachments-modal">
                                    <span class="badge round bg-warning font-size-13"><i
                                            class="bx bx-paperclip"></i></span>
                                </a>
                                <a href="" data-bs-toggle="modal" data-bs-target=".bs-confirm-save-modal">
                                    <span class="badge round bg-success font-size-13"><i class="bx bxs-save"></i></span>
                                </a>
                            </div>
                        </td>

                    </tbody>
                </table>
            </div>


            <div class="modal fade bs-comments-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-top">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="myLargeModalLabel">Comments</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <h5 class="mb-8 text-center">Comment Here...........</h5>

                            <form action="#" method="POST">
                                @csrf
                                <input type="hidden" name="status" value="">
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-success">Add Comment</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade bs-file-attachments-modal" tabindex="-1" role="dialog"
                aria-labelledby="myLargeModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-top">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="myLargeModalLabel">Attach Files</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <h5 class="mb-8 text-center">File Attachments Here...........</h5>

                            <form action="#" method="POST">
                                @csrf
                                <input type="hidden" name="status" value="">
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-success">Add Comment</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade bs-confirm-save-modal" tabindex="-1" role="dialog"
                aria-labelledby="myLargeModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-top">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="myLargeModalLabel">Confirm Save</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <h5 class="mb-8 text-center">Are you sure you want to save the response to this exception?</h5>

                            <form action="#" method="POST">
                                @csrf
                                <input type="hidden" name="status" value="">
                                <div class="d-flex justify-content-center mt-4 gap-2">
                                    <button type="submit" class="btn btn-success">Yes</button>
                                    <button type="submit" class="btn btn-danger" data-bs-dismiss="modal"
                                    aria-label="Close">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{--  <div class="d-flex justify-content-end gap-2 mt-3 mb-5">
                <button type="submit" class="btn btn-success btn-rounded waves-effect waves-light">
                    <i class="bx bx-check"></i> Approve Selected
                </button>
                <button type="submit" class="btn btn-danger btn-rounded waves-effect waves-light">
                    <i class="bx bx-block"></i> Probe Selected
                </button>
            </div>  --}}
        </form>
    </div>

</x-base-layout>
