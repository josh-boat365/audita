<div class="modal fade" id="commentsModal-{{ $exceptionItem->id }}" tabindex="-1"
    aria-labelledby="commentsModalLabel-{{ $exceptionItem->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h5 class="modal-title" id="commentsModalLabel-{{ $exceptionItem->id }}">
                    Comments for Exception #{{ $loop->iteration }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-0">
                <div class="w-100 user-chat">
                    <div class="card border-0">
                        <!-- Chat Header -->
                        <div class="p-4 border-bottom">
                            <div class="row">
                                <div class="col-md-4 col-9">
                                    <h5 class="font-size-15 mb-1">{{ $employeeName }}</h5>
                                    <p class="text-muted mb-0">
                                        <i class="mdi mdi-circle text-success align-middle me-1"></i>
                                        Active now
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Chat Messages -->
                        <div class="chat-conversation p-3">
                            <ul class="list-unstyled mb-0" data-simplebar style="max-height: 400px;">
                                @include('partials.auditee.comments-list', [
                                    'comments' => $exceptionItem->comment ?? [],
                                    'employeeName' => $employeeName,
                                ])
                            </ul>
                        </div>

                        <!-- Chat Input -->
                        @include('partials.auditee.comment-input-form', [
                            'exceptionItem' => $exceptionItem,
                        ])
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
