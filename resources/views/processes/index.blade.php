@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons.css') }}">
<style>
    .process-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .process-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important;
    }
    .btn-designer {
        background: #f0f7ff;
        color: #007bff;
        border: 1px solid #cce5ff;
    }
    .btn-designer:hover {
        background: #007bff;
        color: #fff;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 px-2">
        <div>
            <h4 class="fw-bold mb-0">Workflow Management</h4>
            <p class="text-muted small mb-0">Design and manage your business processes</p>
        </div>
        <a href="{{ route('workflow.processes.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
            <i class="bi bi-plus-lg me-2"></i> Create New Process
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light border-bottom text-uppercase small fw-bold text-muted">
                    <tr>
                        <th class="ps-4 py-3">Process Definition</th>
                        <th class="text-center py-3">Identifier</th>
                        <th class="text-center py-3">Status</th>
                        <th class="text-end pe-4 py-3">Manage</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($processes as $process)
                        <tr>
                            <td class="ps-4 py-3">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-3 bg-primary bg-opacity-10 p-2 me-3 text-primary">
                                        <i class="bi bi-diagram-3 fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark fs-6">{{ $process->name }}</div>
                                        <div class="text-muted small text-truncate" style="max-width: 400px;">
                                            {{ $process->description ?? 'No description provided' }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center py-3">
                                <code class="text-primary fw-medium bg-light px-2 py-1 rounded small">{{ $process->code }}</code>
                            </td>
                            <td class="text-center py-3">
                                @if($process->is_active)
                                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-medium">
                                        <span class="d-inline-block bg-success rounded-circle me-1" style="width: 6px; height: 6px;"></span> Active
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted px-3 py-2 rounded-pill fw-medium border">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                            <td class="text-end pe-4 py-3">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('workflow-designer.show', $process) }}" class="btn btn-designer btn-sm rounded-3 px-3 fw-bold" title="Open Designer">
                                        <i class="bi bi-palette-fill me-1"></i> Designer
                                    </a>
                                    <a href="{{ route('workflow.processes.edit', $process) }}" class="btn btn-outline-primary btn-sm rounded-3 px-3 fw-bold" title="Edit Process">
                                        <i class="bi bi-pencil-square me-1"></i> Edit Process
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <div class="py-4">
                                    <i class="bi bi-folder2-open display-1 text-light"></i>
                                    <h5 class="mt-3 text-muted">No Workflows Found</h5>
                                    <p class="text-muted small">Ready to automate? Create your first workflow process to get started.</p>
                                    <a href="{{ route('workflow.processes.create') }}" class="btn btn-primary rounded-pill px-4 mt-2 shadow-sm">
                                        Create New Process
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($processes->hasPages())
            <div class="card-footer bg-white py-3 border-top-0">
                {{ $processes->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
