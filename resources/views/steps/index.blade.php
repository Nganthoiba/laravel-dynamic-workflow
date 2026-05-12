@extends('workflow::task_layout')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card border-0 shadow-lg rounded-4">
                <div class="card-header bg-primary text-white py-3 rounded-top-4 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-layers-fill me-2"></i>Workflow Steps</h5>
                    <a href="{{ route('workflow.steps.create') }}" class="btn btn-light btn-sm rounded-pill px-3 fw-bold">
                        <i class="bi bi-plus-lg me-1"></i> Create Step
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-uppercase small fw-bold">
                                <tr>
                                    <th class="ps-4">Process</th>
                                    <th>Step Name</th>
                                    <th>Code</th>
                                    <th class="text-center">Stage</th>
                                    <th class="text-center">Start/End</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($steps as $step)
                                    <tr>
                                        <td class="ps-4 py-3">
                                            <span class="text-muted small fw-bold">{{ $step->process->name }}</span>
                                        </td>
                                        <td class="py-3">
                                            <div class="fw-bold text-dark">{{ $step->name }}</div>
                                        </td>
                                        <td class="py-3">
                                            <code class="text-primary">{{ $step->code }}</code>
                                        </td>
                                        <td class="text-center py-3">
                                            <span class="badge bg-light text-dark border rounded-pill px-3">{{ $step->stage_level ?? '-' }}</span>
                                        </td>
                                        <td class="text-center py-3">
                                            @if($step->is_start)
                                                <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 rounded-pill small">Start</span>
                                            @endif
                                            @if($step->is_end)
                                                <span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1 rounded-pill small">End</span>
                                            @endif
                                            @if(!$step->is_start && !$step->is_end)
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center py-3">
                                            @if($step->is_active)
                                                <span class="text-success"><i class="bi bi-check-circle-fill"></i></span>
                                            @else
                                                <span class="text-muted"><i class="bi bi-dash-circle"></i></span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4 py-3">
                                            <div class="btn-group">
                                                <a href="{{ route('workflow.steps.show', $step) }}" class="btn btn-outline-info btn-sm">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('workflow.steps.edit', $step) }}" class="btn btn-outline-warning btn-sm">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form action="{{ route('workflow.steps.destroy', $step) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this step?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($steps->hasPages())
                    <div class="card-footer bg-white py-3">
                        {{ $steps->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
