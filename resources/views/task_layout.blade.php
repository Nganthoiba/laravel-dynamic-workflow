@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons.css') }}">
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <!-- Main Content: Task Execution -->
        <div class="col-md-8">            
            <div class="card shadow-sm border-0">
                <div class="card-header bg-label-primary py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2"></i> Task Information:</h5>
                    <a href="{{ $readonly ?? false ? route('workflow.outbox') : route('workflow.inbox') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Back to {{ $readonly ?? false ? 'Outbox' : 'Inbox' }}
                    </a>
                </div>
                <div class="card-body pt-4">
                    <div class="mb-2">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-3">
                                <span class="fw-bold d-block text-muted small text-uppercase">Process</span>
                                <span class="small">{{ $instance->process->name }}</span>
                            </li>
                            @if(!($readonly ?? false)) 
                            <li class="mb-3">
                                <span class="fw-bold d-block text-muted small text-uppercase">Current Step</span>
                                <span class="text-primary small">{{ $step->name }}</span>
                            </li>
                            @endif
                            
                            <li class="mb-3">
                                <span class="fw-bold d-block text-muted small text-uppercase">Reference ID</span>
                                <span class="text-primary fw-bold small">#{{ $instance->reference_id }}</span>
                            </li>
                            <li>
                                <span class="fw-bold d-block text-muted small text-uppercase">Assigned On</span>
                                <span class="small">{{ $task->created_at->format('M d, Y h:i A') }}</span>
                            </li>
                        </ul>
                </div>

                    <!-- Dynamic Reference Object Details (Config-driven) -->
                    @includeIf($instance->summary_view, ['model' => $model])

                    @if(!($readonly ?? false))
                    <form action="{{ route('workflow.tasks.handle', $task->id) }}" method="POST">
                        @csrf
                    @else
                    <div class="workflow-task-readonly">
                    @endif
                        
                        @if(!($readonly ?? false))    
                        <div class="alert alert-info border-0 shadow-none bg-label-info mb-4">
                            <div class="d-flex">
                                <i class="bi bi-lightbulb-fill me-3 fs-4"></i>
                                <div>
                                    <h6 class="alert-heading mb-1 fw-bold">Instructions</h6>
                                    <p class="mb-0 small">@yield('instructions', $step->description ?? 'Please review the details and provide your decision below.')</p>
                                </div>
                            </div>
                        </div>
                    @endif

                        <!-- Common Form Fields, there can be any number of fields that the programmer/developer want 
                         to fit -->
                        @yield('form_fields')

                        <!-- Default Comment Field if not overridden -->
                        @section('comment_field')
                        <div class="mb-4">
                            <label class="form-label fw-bold">Review Remarks / Comments</label>
                            @if(!($readonly ?? false))
                                <textarea name="comment" class="form-control" rows="4" placeholder="Enter your detailed remarks here..." required></textarea>
                                <div class="form-text">This comment will be visible in the workflow history.</div>
                            @else
                                <div class="p-3 bg-light border rounded min-vh-10">
                                    {{ $task->comment ?? 'No remarks provided.' }}
                                </div>
                            @endif
                        </div>
                        @show

                        <!-- Action Buttons -->
                        @if(!($readonly ?? false))
                        <div class="d-flex justify-content-end gap-2 border-top pt-4">
                            @yield('form_actions')
                        </div>
                        @endif
                    @if(!($readonly ?? false))
                    </form>
                    @else
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar: Status and timeline of steps performed in a workflow -->
        <div class="col-md-4"> 
            <!-- Business Model Summary -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-label-primary py-3">
                    <h6 class="mb-0 fw-bold">Status</h6>
                </div>
                <div class="card-body p-2">
                    <div class="d-flex align-items-center p-2 bg-light rounded border border-dashed mb-4">
                        <i class="bi bi-file-earmark-text fs-3 me-3 text-secondary"></i>
                        <div>
                            <span class="d-block fw-semibold">{{ class_basename($instance->reference_type) }}</span>
                            <small class="text-muted">Status: {{ $instance->status ?? 'Active' }}</small>
                        </div>
                    </div>

                    <!-- Workflow Progress Timeline -->
                    <h6 class="fw-bold mb-3 px-2 text-uppercase small text-muted">Workflow Progress</h6>
                    <div class="workflow-timeline px-2">
                        @foreach($instance->steps->sortBy('created_at') as $history)
                            <div class="timeline-item pb-4 {{ $loop->last ? 'last' : '' }}">
                                <div class="timeline-visual">
                                    <div class="timeline-dot {{ $history->completed_at ? 'bg-success' : 'bg-warning animate-pulse' }}"></div>
                                    @if(!$loop->last)
                                        <div class="timeline-line"></div>
                                    @endif
                                </div>
                                <div class="timeline-content ps-4">
                                    <h6 class="mb-1 fw-bold small text-dark">{{ $history->step->name }}</h6>
                                    
                                    @if($history->completed_at)
                                        <div class="d-flex align-items-center mb-1">
                                            <i class="bi bi-check2-circle text-success me-1 small"></i>
                                            <small class="text-success fw-semibold" style="font-size: 0.75rem;">Completed</small>
                                        </div>
                                        <div class="small text-muted mb-1" style="font-size: 0.7rem;">
                                            <i class="bi bi-person me-1"></i> {{ $history->user->full_name ?? 'System' }}
                                        </div>
                                        <div class="small text-muted mb-1" style="font-size: 0.7rem;">
                                            <i class="bi bi-clock me-1"></i> {{ $history->completed_at->format('M d, Y h:i A') }}
                                        </div>
                                        @if($history->comment)
                                            <div class="mt-2 p-2 bg-light rounded small border-start border-3 border-success" style="font-size: 0.7rem; font-style: italic;">
                                                "{{ $history->comment }}"
                                            </div>
                                        @endif
                                    @else
                                        <div class="d-flex align-items-center mb-1">
                                            <i class="bi bi-hourglass-split text-warning me-1 small"></i>
                                            <small class="text-warning fw-semibold" style="font-size: 0.75rem;">Pending</small>
                                        </div>
                                        <div class="small text-muted" style="font-size: 0.7rem;">
                                            <i class="bi bi-people me-1"></i> 
                                            @if($history->step->roles->count() > 0)
                                                {{ $history->step->roles->pluck('name')->implode(', ') }}
                                            @else
                                                Anyone
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            
            @yield('sidebar_extra')
        </div>
    </div>
</div>

<style>
    .bg-label-primary {
        background-color: #e7e7ff !important;
        color: #696cff !important;
    }
    .bg-label-info {
        background-color: #e1f0ff !important;
        color: #03c3ec !important;
    }

    /* Workflow Timeline Styles */
    .workflow-timeline {
        position: relative;
    }
    .timeline-item {
        position: relative;
        display: flex;
    }
    .timeline-visual {
        position: relative;
        width: 12px;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .timeline-dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        z-index: 2;
        margin-top: 4px;
    }
    .timeline-line {
        position: absolute;
        top: 16px;
        bottom: 0;
        width: 2px;
        background-color: #e9ecef;
        z-index: 1;
    }
    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: .5; }
    }
</style>
@endsection
