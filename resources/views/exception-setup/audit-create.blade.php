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
            <button id="addRowBtn" class="btn btn-success btn-rounded waves-effect waves-light">
                <i class="bx bxs-plus"></i> Add Row
            </button>
        </div>

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
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Rows will be added dynamically here -->
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-3 mb-5">
                <button type="submit" class="btn btn-primary btn-rounded waves-effect waves-light">
                    <i class="bx bx-save"></i> Submit All Exceptions
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                let rowCount = 0;
                const tableBody = document.querySelector('#exceptionsTable tbody');
                const form = document.getElementById('bulkExceptionForm');

                // Add new row
                document.getElementById('addRowBtn').addEventListener('click', function() {
                    rowCount++;
                    const newRow = document.createElement('tr');
                    newRow.dataset.rowId = rowCount;
                    newRow.innerHTML = `
                    <td>${rowCount}</td>
                    <td>
                        <textarea class="form-control editable-textarea"
                                  rows="3"
                                  name="exceptions[${rowCount}][title]"
                                  placeholder="Enter exception title"></textarea>
                    </td>
                    <td>
                        <textarea class="form-control editable-textarea"
                                  rows="3"
                                  name="exceptions[${rowCount}][description]"
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
                    <td>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-success save-row">
                                <i class="bx bxs-save"></i> Save
                            </button>
                            <button type="button" class="btn btn-sm btn-danger delete-row">
                                <i class="bx bxs-trash"></i> Delete
                            </button>
                        </div>
                    </td>
                `;
                    tableBody.appendChild(newRow);

                    // Focus on the first textarea in the new row
                    newRow.querySelector('textarea').focus();
                });

                // Delete row
                tableBody.addEventListener('click', function(e) {
                    if (e.target.closest('.delete-row')) {
                        if (confirm('Are you sure you want to delete this row?')) {
                            const row = e.target.closest('tr');
                            row.remove();
                            // Renumber rows
                            const rows = tableBody.querySelectorAll('tr');
                            rows.forEach((row, index) => {
                                row.cells[0].textContent = index + 1;
                            });
                            rowCount = rows.length;
                        }
                    }

                    // Save row (if you want individual save functionality)
                    if (e.target.closest('.save-row')) {
                        const row = e.target.closest('tr');
                        const title = row.querySelector('[name*="[title]"]').value;
                        const description = row.querySelector('[name*="[description]"]').value;
                        const subCategory = row.querySelector('[name*="[sub_category_id]"]').value;

                        if (!title || !description || !subCategory) {
                            alert('Please fill all fields in this row');
                            return;
                        }

                        // You could add AJAX save here for individual rows if needed
                        alert('Row saved successfully');
                    }
                });

                // Form submission
                form.addEventListener('submit', function(e) {
                    e.preventDefault();

                    // Validate form
                    const rows = tableBody.querySelectorAll('tr');
                    if (rows.length === 0) {
                        alert('Please add at least one exception');
                        return;
                    }

                    const isEmpty = Array.from(rows).some(row => {
                        const title = row.querySelector('[name*="[title]"]').value;
                        const description = row.querySelector('[name*="[description]"]').value;
                        const subCategory = row.querySelector('[name*="[sub_category_id]"]').value;
                        return !title || !description || !subCategory;
                    });

                    if (isEmpty) {
                        alert('Please fill all fields in all rows');
                        return;
                    }

                    // Submit form
                    form.submit();
                });

                // Auto-resize textareas as user types
                document.addEventListener('input', function(e) {
                    if (e.target.classList.contains('editable-textarea')) {
                        autoResizeTextarea(e.target);
                    }
                });

                function autoResizeTextarea(textarea) {
                    textarea.style.height = 'auto';
                    textarea.style.height = (textarea.scrollHeight) + 'px';
                }
            });
        </script>
    @endpush
</x-base-layout>
