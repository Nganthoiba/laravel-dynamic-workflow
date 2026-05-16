@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons.css') }}">
@endsection

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h6 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">Workflow /</span> My Outgoing Task
        </h6>
        <span class="badge bg-label-success fs-6">{{ $tasks->count() }} Completed Tasks</span>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
            <i class="bi bi-send me-2 text-success fs-4"></i>
            <h5 class="mb-0">Actioned by You</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive text-nowrap">
                <table class="table table-hover align-middle mb-0 workflow-outbox-table">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Task Name</th>
                            <th>Process</th>
                            <th>Reference</th>
                            <th>Completed Date</th>
                            <th>Action Taken</th>
                            <th class="text-center">Details</th>
                        </tr>
                    </thead>
                    <tbody class="table-border-bottom-0">
                        @forelse($tasks as $task)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex flex-column">
                                        <span class="fw-bold text-dark" title="{{ $task->step->name }}">
                                            {{ \Illuminate\Support\Str::limit($task->step->name, 30) }}
                                        </span>
                                        <small class="text-muted" title="{{ $task->step->description }}">
                                            {{ \Illuminate\Support\Str::limit($task->step->description ?? 'No description available', 50) }}
                                        </small>
                                    </div>
                                </td>
                                <td title="{{ $task->workflowInstance->process->name }}">
                                    <span class="badge bg-label-info">{{ \Illuminate\Support\Str::limit($task->workflowInstance->process->name, 30) }}</span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-semibold">{{ class_basename($task->workflowInstance->reference_type) }}</span>
                                        <small class="text-primary">#{{ $task->workflowInstance->reference_id }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span>{{ $task->completed_at ? $task->completed_at->format('M d, Y h:i A') : '-' }}</span>
                                        <small class="text-muted">{{ $task->completed_at->diffForHumans() }}</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-label-primary text-uppercase">{{ $task->action ?? 'PROCEEDED' }}</span>
                                </td>
                                <td class="text-center pe-4">
                                    <a href="{{ route('workflow.tasks.show', $task->id) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                        <i class="bi bi-eye me-1"></i> View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bi bi-journal-check fs-1 text-muted mb-3"></i>
                                        <h5 class="text-muted">No completed tasks yet</h5>
                                        <p class="text-muted small">Tasks you finish will appear here.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-label-primary {
        background-color: #e7e7ff !important;
        color: #696cff !important;
    }
    .bg-label-success {
        background-color: #e8fadf !important;
        color: #71dd37 !important;
    }
    .bg-label-info {
        background-color: #e1f0ff !important;
        color: #03c3ec !important;
    }
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
        cursor: pointer;
    }
</style>
@endsection

@push('styles')
<style>
    .workflow-outbox-table {
        font-size: 0.85rem;
    }
    .workflow-outbox-table th {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .workflow-outbox-table .badge {
        font-size: 0.75rem;
    }
</style>
@endpush
