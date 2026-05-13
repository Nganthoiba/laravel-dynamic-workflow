@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card border-0 shadow-lg rounded-4">
                <div class="card-header bg-primary text-white py-3 rounded-top-4 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-diagram-3-fill me-2"></i>Workflow Processes</h5>
                    <a href="{{ route('workflow.processes.create') }}" class="btn btn-light btn-sm rounded-pill px-3 fw-bold">
                        <i class="bi bi-plus-lg me-1"></i> Create Process
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light text-uppercase small fw-bold">
                                <tr>
                                    <th class="ps-4">Process Details</th>
                                    <th class="text-center">Code</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($processes as $process)
                                    <tr>
                                        <td class="ps-4 py-3">
                                            <div class="fw-bold text-dark fs-6">{{ $process->name }}</div>
                                            <div class="text-muted small text-truncate" style="max-width: 300px;">{{ $process->description ?? 'No description available' }}</div>
                                        </td>
                                        <td class="text-center py-3">
                                            <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-2 rounded-3 fw-semibold">
                                                {{ $process->code }}
                                            </span>
                                        </td>
                                        <td class="text-center py-3">
                                            @if($process->is_active)
                                                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-medium">
                                                    <i class="bi bi-check-circle-fill me-1"></i> Active
                                                </span>
                                            @else
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill fw-medium">
                                                    <i class="bi bi-dash-circle-fill me-1"></i> Inactive
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4 py-3">
                                            <div class="btn-group shadow-sm rounded-3 overflow-hidden">
                                                <a href="{{ route('workflow-designer.show', $process) }}" class="btn btn-outline-primary btn-sm px-3" title="Design Workflow">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                <a href="{{ route('workflow.processes.edit', $process) }}" class="btn btn-outline-secondary btn-sm px-3" title="Edit Properties">
                                                    <i class="bi bi-gear"></i>
                                                </a>
                                                <form action="{{ route('workflow.processes.destroy', $process) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this process?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm px-3" title="Delete">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted italic">
                                            No processes found. Start by creating a new one.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($processes->hasPages())
                    <div class="card-footer bg-white py-3 rounded-bottom-4">
                        {{ $processes->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
