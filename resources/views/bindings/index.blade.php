{{--
    View: Bindings Index Dashboard
    
    Purpose:
    Renders the workflow bindings dashboard. Displays a table of all active and inactive bindings 
    mapping processes to models and events, including trigger type, priority, and quick toggle/edit/delete actions.
    
    Usage:
    Returned by WorkflowBindingController@index, accessed via route('workflow-bindings.index').
    Extends layouts.app and expects Bootstrap 5 & Bootstrap Icons to be loaded.
--}}

@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons.css') }}">
<style>
    .btn-toggle-active {
        transition: all 0.2s ease-in-out;
    }
    .btn-toggle-active:hover {
        transform: scale(1.05);
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4">
    <!-- Header Section -->
    <div class="d-flex justify-content-between align-items-center mb-4 px-2">
        <div>
            <h4 class="fw-bold mb-0">Workflow Bindings</h4>
            <p class="text-muted small mb-0">Map business models and lifecycle events to automated workflow processes</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('workflow.processes.index') }}" class="btn btn-outline-secondary rounded-pill px-4 shadow-sm">
                <i class="bi bi-arrow-left me-2"></i> Manage Processes
            </a>
            <a href="{{ route('workflow-bindings.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
                <i class="bi bi-plus-lg me-2"></i> Create New Binding
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 border-0 shadow-sm mb-4 px-4 py-3" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill fs-5 me-3 text-success"></i>
                <div>
                    {{ session('success') }}
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Bindings Table Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light border-bottom text-uppercase small fw-bold text-muted">
                    <tr>
                        <th class="ps-4 py-3">Workflow Process</th>
                        <th class="py-3">Business Model</th>
                        <th class="py-3">Lifecycle Event</th>
                        <th class="text-center py-3">Priority</th>
                        <th class="text-center py-3">Status</th>
                        <th class="text-end pe-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bindings as $binding)
                        <tr>
                            <!-- Process Name -->
                            <td class="ps-4 py-3">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-3 bg-primary bg-opacity-10 p-2 me-3 text-primary">
                                        <i class="bi bi-diagram-3-fill fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark fs-6">{{ $binding->process->name ?? 'Unknown Process' }}</div>
                                        <div class="text-muted small">ID: {{ $binding->process_id }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Model Label -->
                            <td class="py-3">
                                <span class="fw-medium text-dark">{{ $registry->getModelLabel($binding->model_type) }}</span>
                                <div class="text-muted small text-truncate" style="max-width: 250px;" title="{{ $binding->model_type }}">
                                    {{ $binding->model_type }}
                                </div>
                            </td>

                            <!-- Event Name -->
                            <td class="py-3">
                                <span class="badge bg-light text-secondary border px-2 py-1.5 rounded fw-medium">
                                    {{ $registry->getEventLabel($binding->event_name) }}
                                </span>
                            </td>

                            <!-- Priority -->
                            <td class="text-center py-3">
                                <span class="badge bg-light text-dark px-2.5 py-1.5 rounded fw-bold border">
                                    {{ $binding->priority }}
                                </span>
                            </td>

                            <!-- Status (Toggle active form) -->
                            <td class="text-center py-3">
                                <form action="{{ route('workflow-bindings.toggle', $binding->id) }}" method="POST" class="d-inline-block">
                                    @csrf
                                    @if($binding->is_active)
                                        <button type="submit" class="btn btn-link p-0 text-decoration-none btn-toggle-active" title="Click to Deactivate">
                                            <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-medium">
                                                <span class="d-inline-block bg-success rounded-circle me-1" style="width: 6px; height: 6px;"></span> Active
                                            </span>
                                        </button>
                                    @else
                                        <button type="submit" class="btn btn-link p-0 text-decoration-none btn-toggle-active" title="Click to Activate">
                                            <span class="badge bg-light text-muted px-3 py-2 rounded-pill fw-medium border">
                                                Inactive
                                            </span>
                                        </button>
                                    @endif
                                </form>
                            </td>

                            <!-- Actions -->
                            <td class="text-end pe-4 py-3">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('workflow-bindings.edit', $binding->id) }}" class="btn btn-outline-primary btn-sm rounded-3 px-3 fw-bold" title="Edit Binding">
                                        <i class="bi bi-pencil-square me-1"></i> Edit
                                    </a>
                                    <form action="{{ route('workflow-bindings.destroy', $binding->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this workflow binding?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm rounded-3 px-3 fw-bold" title="Delete Binding">
                                            <i class="bi bi-trash-fill me-1"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="py-4">
                                    <i class="bi bi-link-45deg display-1 text-light"></i>
                                    <h5 class="mt-3 text-muted">No Bindings Set Up</h5>
                                    <p class="text-muted small">Bind your workflow definitions to business model events to enable dynamic, automated triggers.</p>
                                    <a href="{{ route('workflow-bindings.create') }}" class="btn btn-primary rounded-pill px-4 mt-2 shadow-sm">
                                        Create Your First Binding
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($bindings->hasPages())
            <div class="card-footer bg-white py-3 border-top-0">
                {{ $bindings->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
