@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <!-- Sidebar: Task Info -->
        <div class="col-md-4">
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-header bg-label-primary py-3">
                    <h5 class="mb-0 text-primary fw-bold"><i class="bi bi-info-circle me-2"></i>Task Information</h5>
                </div>
                <div class="card-body pt-4">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3">
                            <span class="fw-bold d-block text-muted small text-uppercase">Process</span>
                            <span class="small">{{ $instance->process->name }}</span>
                        </li>
                        <li class="mb-3">
                            <span class="fw-bold d-block text-muted small text-uppercase">Current Step</span>
                            <span class="text-primary small">{{ $step->name }}</span>
                        </li>
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
            </div>

            <!-- Business Model Summary -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light py-3">
                    <h6 class="mb-0 fw-bold">Reference Details</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center p-2 bg-light rounded border border-dashed">
                        <i class="bi bi-file-earmark-text fs-3 me-3 text-secondary"></i>
                        <div>
                            <span class="d-block fw-semibold">{{ class_basename($instance->reference_type) }}</span>
                            <small class="text-muted">Status: {{ $model->status ?? 'Active' }}</small>
                        </div>
                    </div>
                </div>
            </div>
            
            @yield('sidebar_extra')
        </div>

        <!-- Main Content: Task Execution -->
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Transaction Detail:</h5>
                    <a href="{{ route('workflow.inbox') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Back to Inbox
                    </a>
                </div>
                <div class="card-body pt-4">
                    <!-- Dynamic Reference Object Details (Config-driven) -->
                    @includeIf($instance->summary_view, ['model' => $model])

                    <form action="{{ route('workflow.tasks.handle', $task->id) }}" method="POST">
                        @csrf
                        
                        <div class="alert alert-info border-0 shadow-none bg-label-info mb-4">
                            <div class="d-flex">
                                <i class="bi bi-lightbulb-fill me-3 fs-4"></i>
                                <div>
                                    <h6 class="alert-heading mb-1 fw-bold">Instructions</h6>
                                    <p class="mb-0 small">@yield('instructions', $step->description ?? 'Please review the details and provide your decision below.')</p>
                                </div>
                            </div>
                        </div>

                        <!-- Common Form Fields, there can be any number of fields that the programmer/developer want 
                         to fit -->
                        @yield('form_fields')

                        <!-- Default Comment Field if not overridden -->
                        @section('comment_field')
                        <div class="mb-4">
                            <label class="form-label fw-bold">Review Remarks / Comments</label>
                            <textarea name="comment" class="form-control" rows="4" placeholder="Enter your detailed remarks here..." required></textarea>
                            <div class="form-text">This comment will be visible in the workflow history.</div>
                        </div>
                        @show

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-end gap-2 border-top pt-4">
                            @yield('form_actions')
                        </div>
                    </form>
                </div>
            </div>
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
</style>
@endsection
