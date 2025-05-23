<x-base-layout>
    <div class="container-fluid px-1">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18">Bulk Exception Create</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="d-flex gap-2 mb-4 justify-content-end">
            <a href="{{ route('exception.create') }}" class="btn btn-primary btn-rounded waves-effect waves-light "><i
                    class="bx bxs-plus"></i>Add Sub-Category</a>
            <a href="{{ route('exception.create') }}" class="btn btn-success btn-rounded waves-effect waves-light "><i
                    class="bx bxs-plus"></i>Add</a>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center g-2">
                            <div class="col-md-3">
                                <label class="form-label">Batch</label>
                                <select class="form-select select2" name="exceptionBatchId">
                                    <option>Select.....</option>
                                    <option value="">sdsdfsdfsdfsf</option>
                                </select>
                            </div>


                            <div class="col-md-3">
                                <label class="form-label">Category</label>
                                <select class="form-select select2" name="exceptionBatchId">
                                    <option>Select.....</option>
                                    <option value="">sdsdfsdfsdfsf</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Department</label>
                                <select class="form-select select2" name="exceptionBatchId">
                                    <option>Select.....</option>
                                    <option value="">sdsdfsdfsdfsf</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Ocurrence Date</label>
                                <select class="form-select select2" name="exceptionBatchId">
                                    <option>Select.....</option>
                                    <option value="">sdsdfsdfsdfsf</option>
                                </select>
                            </div>

                        </div>

                    </div>
                </div>
            </div>
        </div>


        <div class="mt-4 mb-4" style="background-color: gray; height: 1px;"></div>


        <div class="table-responsive">
            <table class="table table-bordered  table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Exception Title</th>
                        <th>Exception Description</th>
                        <th>Sub Category</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>

                    <tr>
                        <th scope="row"><a href="#">Teller Operations</a></th>

                        <td>
                            <a href="javascript: void(0);" id="inline-comments" data-type="textarea" data-pk="1"
                                data-placeholder="Your comments here..." data-title="Enter comments">
                                This is a sample exception description. It provides details about the exception that
                                occurred
                                during the process. The description should be clear and concise, outlining the nature of
                                the
                                exception and any relevant information that may help in understanding the issue.
                            </a>

                        </td>

                        <td>
                            <span class="dropdown badge rounded-pill bg-primary">
                                Teller and Cash
                            </span>
                        </td>

                        <td>
                            <div class="d-flex gap-3">
                                <a href="{{ route('exception.edit', 1) }}">
                                    <span class="badge round bg-success font-size-13">
                                        {{--  <i class="bx bxs-pencil"></i>  --}}
                                        Save</span>
                                </a>
                                {{--  DELETE BUTTON  --}}

                            </div>
                        </td>
                    </tr>


                </tbody>
            </table>

        </div>

    </div>

</x-base-layout>
