<x-base-layout>
    <div class="container-fluid px-1">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0 font-size-18"><a href="{{ route('exception.supervisor.list') }}">Exceptions</a> > Micheal Asenso > Sweduru > <a href="#">Operations</a></h4>
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
                            <th scope="col"><input type="checkbox" name="approve_all" id=""></th>
                            <th scope="col">#</th>
                            <th scope="col">Exception Title</th>
                            <th scope="col">Exception Description</th>
                            <th scope="col">Sub Category</th>
                            {{--  <th scope="col">Action</th>  --}}
                        </tr>
                    </thead>
                    <tbody>
                        <td><input type="checkbox" name="approve_all" id=""></td>
                        <td>1</td>
                        <td>
                            <textarea class="form-control editable-textarea" rows="3" name="exceptions[${rowCount}][title]"
                                placeholder="Enter exception title"></textarea>
                        </td>
                        <td>
                            <textarea class="form-control editable-textarea" rows="3" name="exceptions[${rowCount}][description]"
                                placeholder="Enter exception description"></textarea>
                        </td>
                        <td>
                            <select class="form-select" name="exceptions[${rowCount}][sub_category_id]">
                                <option value="">Select...</option>
                                {{--  @foreach ($subCategories as $subCat)  --}}
                                {{--  <option value="{{ $subCat->id }}">{{ $subCat->name }}</option>  --}}
                                {{--  @endforeach  --}}
                            </select>
                        </td>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-3 mb-5">
                <button type="submit" class="btn btn-success btn-rounded waves-effect waves-light">
                    <i class="bx bx-check"></i> Approve Selected
                </button>
                <button type="submit" class="btn btn-danger btn-rounded waves-effect waves-light">
                    <i class="bx bx-sync"></i> Probe Selected
                </button>
            </div>
        </form>
    </div>

</x-base-layout>
