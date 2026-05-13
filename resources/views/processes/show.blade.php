@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row g-4">
        <!-- Process Overview Header -->
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-body p-0">
                    <div class="row g-0">
                        <div class="col-md-8 p-4">
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-2 small fw-bold text-uppercase">
                                    <li class="breadcrumb-item"><a href="{{ route('workflow.processes.index') }}" class="text-decoration-none">Processes</a></li>
                                    <li class="breadcrumb-item active" aria-current="page">Details</li>
                                </ol>
                            </nav>
                            <h2 class="fw-bold text-dark mb-1">{{ $process->name }}</h2>
                            <p class="text-muted mb-3">{{ $process->description ?: 'No description provided for this process.' }}</p>
                            
                            <div class="d-flex gap-3 align-items-center mb-2">
                                <span class="badge bg-light text-primary border border-primary-subtle px-3 py-2 rounded-pill">
                                    <i class="bi bi-code-square me-1"></i> {{ $process->code }}
                                </span>
                                @if($process->is_active)
                                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill border border-success-subtle">
                                        <i class="bi bi-check-circle-fill me-1"></i> Active
                                    </span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill border border-secondary-subtle">
                                        <i class="bi bi-dash-circle-fill me-1"></i> Inactive
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4 bg-light border-start d-flex flex-column justify-content-center p-4">
                            <div class="d-grid gap-2">
                                <a href="{{ route('workflow-designer.show', $process) }}" class="btn btn-primary rounded-3 py-2 fw-bold">
                                    <i class="bi bi-pencil-square me-2"></i> Launch Designer
                                </a>
                                <a href="{{ route('workflow.processes.edit', $process) }}" class="btn btn-outline-secondary rounded-3 py-2">
                                    <i class="bi bi-gear me-2"></i> Edit Properties
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Steps Timeline/List -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold text-dark">Workflow Steps</h5>
                    <span class="badge bg-primary rounded-pill">{{ $process->steps->count() }} Steps</span>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($process->steps->sortBy('stage_level') as $step)
                            <div class="list-group-item py-3 px-4 border-bottom-0 hover-bg-light transition-all">
                                <div class="d-flex align-items-start">
                                    <div class="avatar-sm rounded-circle {{ $step->is_start ? 'bg-success' : ($step->is_end ? 'bg-danger' : 'bg-primary') }} text-white d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 36px; height: 36px; flex-shrink: 0;">
                                        @if($step->is_start) <i class="bi bi-play-fill"></i>
                                        @elseif($step->is_end) <i class="bi bi-stop-fill"></i>
                                        @else <span class="fw-bold">{{ $loop->iteration }}</span>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="fw-bold mb-1 text-dark">{{ $step->name }}</h6>
                                            <code class="small text-secondary">{{ $step->code }}</code>
                                        </div>
                                        <p class="text-muted small mb-2">{{ Str::limit($step->description, 100) }}</p>
                                        
                                        <div class="d-flex gap-2 align-items-center flex-wrap mt-2">
                                            <span class="badge bg-light text-dark border small rounded-pill px-2">
                                                <i class="bi bi-eye-fill me-1"></i> {{ $step->workflow_action }}
                                            </span>
                                            @if($step->stage_level)
                                                <span class="badge bg-light text-dark border small rounded-pill px-2">
                                                    Stage {{ $step->stage_level }}
                                                </span>
                                            @endif
                                            @foreach($step->roles as $role)
                                                <span class="badge bg-info-subtle text-info border border-info-subtle small rounded-pill px-2">
                                                    {{ $role->display_name }}
                                                </span>
                                            @endforeach
                                        </div>

                                        <!-- Outgoing Transitions -->
                                        @if($step->outgoingEdges->count() > 0)
                                            <div class="mt-3 bg-light rounded-3 p-2 border border-dashed border-secondary-subtle">
                                                <small class="text-secondary fw-bold text-uppercase d-block mb-1 px-1" style="font-size: 0.65rem;">Transitions From Here</small>
                                                <div class="d-flex flex-wrap gap-2">
                                                    @foreach($step->outgoingEdges as $transition)
                                                        <div class="d-flex align-items-center bg-white rounded-2 px-2 py-1 shadow-sm border border-light">
                                                            <span class="small fw-bold text-dark me-2">{{ $transition->action_label }}</span>
                                                            <i class="bi bi-arrow-right text-secondary small me-2"></i>
                                                            <span class="small text-primary fw-medium">{{ $transition->toStep->name }}</span>
                                                            @if($transition->is_default)
                                                                <span class="ms-2 badge bg-light text-muted border-0 small px-1" title="Default Fallback"><i class="bi bi-flag-fill"></i></span>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-5 text-center text-muted">
                                <i class="bi bi-layers fs-1"></i>
                                <p class="mt-3">No steps defined for this process yet.</p>
                                <a href="{{ route('workflow-designer.show', $process) }}" class="btn btn-primary btn-sm rounded-pill mt-2">Open Designer</a>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar/Stats -->
        <div class="col-md-4">
            <div class="row g-4">
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h5 class="mb-0 fw-bold text-dark">Instance Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                                <span class="text-muted">Total Instances</span>
                                <span class="fw-bold">{{ $process->workflowInstances->count() }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3 border-bottom pb-2">
                                <span class="text-muted text-success">Active</span>
                                <span class="fw-bold">{{ $process->workflowInstances->where('status', 'active')->count() }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-0">
                                <span class="text-muted text-danger">Completed</span>
                                <span class="fw-bold">{{ $process->workflowInstances->where('status', 'completed')->count() }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 text-center mt-auto">
                    <div class="p-3 bg-light rounded-4 border border-dashed text-muted small">
                        <i class="bi bi-info-circle me-1"></i> This process was created {{ $process->created_at->diffForHumans() }}.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hover-bg-light:hover {
        background-color: rgba(0,0,0,0.015);
    }
    .transition-all {
        transition: all 0.2s ease-in-out;
    }
    .border-dashed {
        border-style: dashed !important;
    }
</style>
@endsection
