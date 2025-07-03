@push('scripts')
    <script>
        $(document).ready(function() {
            // ========== COMMENT FUNCTIONALITY ==========

            // Handle comment form submission
            $('.comment-form').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const formData = form.serialize();

                $.ajax({
                    url: form.attr('action'),
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            // Clear the input
                            form.find('input[name="comment"]').val('');

                            // Show success message
                            toastr.success('Comment added successfully');

                            // Refresh the comments section
                            refreshCommentsSection(response.exceptionId);
                        }
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Error adding comment');
                    }
                });
            });

            // Handle edit comment
            $(document).on('click', '.edit-comment-btn', function(e) {
                e.preventDefault();
                const commentId = $(this).data('comment-id');
                const commentText = $(this).data('comment-text');

                $('#editCommentForm textarea').val(commentText);
                $('#editCommentForm').attr('action', `/comments/${commentId}`);
                $('#editCommentModal').modal('show');
            });

            // Handle delete comment
            $(document).on('click', '.delete-comment-btn', function(e) {
                e.preventDefault();
                const commentId = $(this).data('comment-id');

                $('#deleteCommentForm').attr('action', `/comments/${commentId}`);
                $('#deleteCommentModal').modal('show');
            });

            // ========== FILE UPLOAD FUNCTIONALITY ==========
            // Initialize Dropzone




            function refreshAttachmentsSection(exceptionId) {
                const attachmentsModal = document.querySelector(`#fileAttachmentsModal-${exceptionId}`);
                if (attachmentsModal) {
                    $.get(window.location.href, function(data) {
                        const newContent = $(data).find(`#fileAttachmentsModal-${exceptionId}`)
                            .html();
                        $(attachmentsModal).html(newContent);
                        updateAttachmentCount(exceptionId);
                    });
                }
            }

            function updateAttachmentCount(exceptionId) {
                const count = document.querySelectorAll(
                    `#fileAttachmentsModal-${exceptionId} .file-info`).length;
                const badge = document.querySelector(`#fileAttachmentsModal-${exceptionId} .badge`);
                if (badge) {
                    badge.textContent = count;
                }
            }
        });


        // ========== UTILITY FUNCTIONS ==========







        function formatBytes(bytes, decimals = 2) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) /
                Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        }

        function
        refreshCommentsSection(exceptionId) {
            const commentsModal = $(`#commentsModal-${exceptionId}`);
            if (commentsModal.length) {
                $.get(window.location.href, function(data) {
                    const
                        newContent = $(data).find(`#commentsModal-${exceptionId}`).html();
                    commentsModal.html(newContent); // Update comment
                    count badge updateCommentCount(exceptionId);
                });
            }
        }

        function updateCommentCount(exceptionId) { // Update the
            comment count badge in the action buttons
            const
                commentsButton = $(
                    `button[data-bs-target="#commentsModal-${exceptionId}"]`); // Implementation depends on your comment
            count logic
        } //==========KEYBOARD SHORTCUTS==========// Ctrl+Enter to send comment $('.chat-input').on('keydown',
        function(e) {
            if (e.ctrlKey && e.which === 13) {
                $(this).closest('form').submit();
            }
        }); // ESC to close modals
        $(document).on('keydown', function(e) {
        if (e.which === 27) {
            $('.modal.show').modal('hide');
        }
        });
        });
    </script>
@endpush
