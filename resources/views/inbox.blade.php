@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons.css') }}">
@endsection

@section('content')
<div class="container-fluid flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h6 class="fw-bold py-3 mb-0">
            <span class="text-muted fw-light">Workflow /</span> My Incoming Task
        </h6>
        <span class="badge bg-label-primary fs-6">{{ $tasks->count() }} Pending Tasks</span>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center">
            <i class="bi bi-inbox me-2 text-primary fs-4"></i>
            <h5 class="mb-0">Awaiting Your Action</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive text-nowrap">
                <table class="table table-hover align-middle mb-0 workflow-inbox-table">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Task Name</th>
                            <th>Process</th>
                            <th>Reference</th>
                            <th>Assigned Date</th>
                            <th class="text-center">Action</th>
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
                                </</td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-semibold">{{ class_basename($task->workflowInstance->reference_type) }}</span>
                                        <small class="text-primary">#{{ $task->workflowInstance->reference_id }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span>{{ $task->entered_at ? $task->entered_at->format('M d, Y') : $task->created_at->format('M d, Y') }}</span>
                                        <small class="text-muted">{{ $task->created_at->diffForHumans() }}</small>
                                    </div>
                                </td>
                                <td class="text-center pe-4">
                                    <a href="{{ route('workflow.tasks.show', $task->id) }}" class="btn btn-primary btn-sm rounded-pill px-3">
                                        <i class="bi bi-play-fill me-1"></i> Open Task
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="bi bi-check2-circle fs-1 text-success mb-3"></i>
                                        <h5 class="text-muted">No pending tasks in your inbox</h5>
                                        <p class="text-muted small">You're all caught up!</p>
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
    .workflow-inbox-table {
        font-size: 0.85rem;
    }
    .workflow-inbox-table th {
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .workflow-inbox-table .badge {
        font-size: 0.75rem;
    }
</style>
@endpush
