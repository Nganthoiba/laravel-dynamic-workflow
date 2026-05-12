@extends('workflow::task_layout')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card border-0 shadow-lg rounded-4">
                <div class="card-header bg-primary text-white py-3 rounded-top-4 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-shuffle me-2"></i>Step Transitions</h5>
                    <a href="{{ route('workflow.step-transitions.create') }}" class="btn btn-light btn-sm rounded-pill px-3 fw-bold">
                        <i class="bi bi-plus-lg me-1"></i> Create Transition
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-uppercase small fw-bold">
                                <tr>
                                    <th class="ps-4">Process</th>
                                    <th>From Step</th>
                                    <th class="text-center"><i class="bi bi-arrow-right"></i></th>
                                    <th>To Step</th>
                                    <th>Action Label</th>
                                    <th class="text-center">Priority</th>
                                    <th class="text-center">Default</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transitions as $transition)
                                    <tr>
                                        <td class="ps-4 py-3">
                                            <span class="text-muted small fw-bold">{{ $transition->process->name }}</span>
                                        </td>
                                        <td class="py-3">
                                            <div class="fw-bold text-dark">{{ $transition->fromStep->name }}</div>
                                        </td>
                                        <td class="text-center py-3">
                                            <i class="bi bi-arrow-right text-primary"></i>
                                        </td>
                                        <td class="py-3">
                                            <div class="fw-bold text-dark">{{ $transition->toStep->name }}</div>
                                        </td>
                                        <td class="py-3">
                                            <span class="badge bg-light text-primary border px-2 py-1">{{ $transition->action_label }}</span>
                                        </td>
                                        <td class="text-center py-3">
                                            {{ $transition->priority }}
                                        </td>
                                        <td class="text-center py-3">
                                            @if($transition->is_default)
                                                <span class="text-success"><i class="bi bi-flag-fill"></i></span>
                                            @else
                                                <span class="text-muted"><i class="bi bi-flag"></i></span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4 py-3">
                                            <div class="btn-group">
                                                <a href="{{ route('workflow.step-transitions.show', $transition) }}" class="btn btn-outline-info btn-sm">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('workflow.step-transitions.edit', $transition) }}" class="btn btn-outline-warning btn-sm">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <form action="{{ route('workflow.step-transitions.destroy', $transition) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this transition?')">
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
                @if($transitions->hasPages())
                    <div class="card-footer bg-white py-3">
                        {{ $transitions->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
