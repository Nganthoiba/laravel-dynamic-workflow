@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-4 shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center bg-label-primary py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-info-circle me-2"></i>Workflow Instance Details</h5>
                    <a href="{{ route('workflow.history', $instance->id) }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-clock-history me-1"></i> View History
                    </a>
                </div>
                <div class="card-body pt-4">
                    <div class="row g-4">
                        <!-- Left Column: Basic Info -->
                        <div class="col-lg-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar avatar-md me-3">
                                    <span class="avatar-initial rounded bg-label-info"><i class="bi bi-diagram-3"></i></span>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">Process Information</h6>
                                    <small class="text-muted">General workflow metadata</small>
                                </div>
                            </div>
                            <ul class="list-group list-group-flush border-top">
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span class="text-muted small">Instance ID</span>
                                    <span class="fw-semibold">#{{ $instance->id }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span class="text-muted small">Process Name</span>
                                    <span class="fw-semibold">{{ $instance->process->name ?? 'N/A' }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span class="text-muted small">Reference Type</span>
                                    <span class="badge bg-label-secondary">{{ class_basename($instance->reference_type) }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span class="text-muted small">Reference ID</span>
                                    <span class="fw-semibold text-primary">{{ $instance->reference_id }}</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Right Column: Status Info -->
                        <div class="col-lg-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar avatar-md me-3">
                                    <span class="avatar-initial rounded bg-label-success"><i class="bi bi-activity"></i></span>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold">Current Status & Timeline</h6>
                                    <small class="text-muted">Real-time execution state</small>
                                </div>
                            </div>
                            <ul class="list-group list-group-flush border-top">
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span class="text-muted small">Current Status</span>
                                    @php
                                        $statusColors = [
                                            'IN_PROGRESS' => 'primary',
                                            'completed' => 'success',
                                            'cancelled' => 'danger',
                                        ];
                                        $color = $statusColors[$instance->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $color }}">{{ strtoupper($instance->status) }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span class="text-muted small">Current Step</span>
                                    @if($instance->currentStep)
                                        <span class="text-primary fw-bold">{{ $instance->currentStep->name }}</span>
                                    @else
                                        <span class="text-muted italic">Completed</span>
                                    @endif
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span class="text-muted small">Started At</span>
                                    <span class="fw-semibold small text-dark">{{ $instance->created_at->format('d M Y, h:i A') }}</span>
                                </li>
                                @if($instance->completed_at)
                                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                    <span class="text-muted small">Completed At</span>
                                    <span class="fw-semibold small text-success">{{ \Carbon\Carbon::parse($instance->completed_at)->format('d M Y, h:i A') }}</span>
                                </li>
                                @endif
                            </ul>
                        </div>
                    </div>

                    <!-- Next Action Banner -->
                    @if($instance->status === 'IN_PROGRESS' && $instance->current_step_id)
                    <div class="mt-4 p-4 rounded bg-label-primary border border-primary border-dashed">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-lightning-charge-fill fs-3 text-primary me-3"></i>
                                <div>
                                    <h6 class="mb-1 fw-bold text-primary">Awaiting Action</h6>
                                    <p class="mb-0 text-muted small">This workflow is waiting for processing at the <strong>{{ $instance->currentStep->name }}</strong> stage.</p>
                                </div>
                            </div>
                            @php
                                $openTask = $instance->steps->whereNull('completed_at')->first();
                            @endphp
                            @if($openTask)
                            <a href="{{ route('workflow.tasks.show', $openTask->id) }}" class="btn btn-primary shadow">
                                <i class="bi bi-play-fill me-1"></i> Continue Workflow
                            </a>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
                <div class="card-footer bg-light border-top text-center py-3">
                    <button onclick="window.history.back()" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> Back to Previous Page
                    </button>
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
    .bg-label-secondary {
        background-color: #ebeef1 !important;
        color: #8592a3 !important;
    }
</style>
@endsection
