@forelse ($comments as $comment)
    <li class="{{ $comment->createdBy == $employeeName ? 'right' : 'left' }}">
        <div class="conversation-list">
            <!-- Dropdown Menu (only for own comments) -->
            @if ($comment->createdBy == $employeeName)
                <div class="dropdown">
                    <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="bx bx-dots-vertical-rounded"></i>
                    </a>
                    <div class="dropdown-menu">
                        <a class="dropdown-item edit-comment-btn" href="#"
                           data-comment-id="{{ $comment->id }}"
                           data-comment-text="{{ $comment->comment }}">
                            <i class="bx bx-edit me-2"></i>Edit
                        </a>
                        <a class="dropdown-item delete-comment-btn text-danger" href="#"
                           data-comment-id="{{ $comment->id }}">
                            <i class="bx bx-trash me-2"></i>Delete
                        </a>
                    </div>
                </div>
            @endif

            <!-- Comment Content -->
            <div class="ctext-wrap">
                <div class="conversation-name fw-bold">{{ $comment->createdBy }}</div>
                <p class="mb-1">{{ $comment->comment }}</p>
                <p class="chat-time mb-0 text-muted small">
                    <i class="bx bx-time-five align-middle me-1"></i>
                    {{ Carbon\Carbon::parse($comment->createdAt)->format('d/m/Y H:i A') }}
                </p>
            </div>
        </div>
    </li>
@empty
    <li>
        <div class="chat-day-title">
            <span class="title text-muted">No comments yet. Be the first to comment!</span>
        </div>
    </li>
@endforelse
