<div class="p-3 chat-input-section border-top">
    <form action="{{ route('exception.comment.post', $exceptionItem->id) }}" method="POST" class="comment-form">
        @csrf
        <div class="row g-2">
            <div class="col">
                <div class="position-relative">
                    <input type="text" name="comment" class="form-control chat-input" placeholder="Type your comment..."
                        required maxlength="500">
                    <div class="invalid-feedback">Please enter a comment.</div>
                </div>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-rounded chat-send waves-effect waves-light">
                    <span class="d-none d-sm-inline-block me-2">Send</span>
                    <i class="mdi mdi-send"></i>
                </button>
            </div>
        </div>
        <small class="text-muted">Press Ctrl+Enter to send quickly</small>
    </form>
</div>
