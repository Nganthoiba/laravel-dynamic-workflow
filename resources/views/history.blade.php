@extends('layouts.app')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex justify-content-between align-items-center bg-label-info py-3">
                    <h5 class="mb-0 fw-bold text-info"><i class="bi bi-clock-history me-2"></i>Workflow Execution History</h5>
                    <div>
                        <a href="{{ route('workflow.details', $instance->id) }}" class="btn btn-outline-info btn-sm me-2">
                            <i class="bi bi-info-circle me-1"></i> Instance Details
                        </a>
                        <button onclick="window.history.back()" class="btn btn-info btn-sm">
                            <i class="bi bi-arrow-left me-1"></i> Back
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4 py-3 text-muted small text-uppercase fw-bold">Step Name</th>
                                    <th class="py-3 text-muted small text-uppercase fw-bold">Action Performed</th>
                                    <th class="py-3 text-muted small text-uppercase fw-bold">Executed By</th>
                                    <th class="py-3 text-muted small text-uppercase fw-bold">Date & Time</th>
                                    <th class="py-3 text-muted small text-uppercase fw-bold text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($history as $item)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="step-icon me-3">
                                                <span class="badge bg-label-info p-2 rounded-circle">
                                                    <i class="bi bi-geo-alt"></i>
                                                </span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-semibold">{{ $item->step->name ?? 'Unknown Step' }}</h6>
                                                <small class="text-muted text-truncate" style="max-width: 200px; display: block;">
                                                    ID: {{ $item->step_id }}
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($item->action)
                                            <span class="badge bg-label-primary rounded-pill text-capitalize px-3">
                                                {{ str_replace('_', ' ', $item->action) }}
                                            </span>
                                        @else
                                            <span class="text-muted small italic">No action recorded</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-xs me-2">
                                                <span class="avatar-initial rounded-circle bg-label-secondary">
                                                    {{ strtoupper(substr($item->user->name ?? 'U', 0, 1)) }}
                                                </span>
                                            </div>
                                            <span class="fw-medium">{{ $item->user->name ?? 'System' }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold text-dark">{{ $item->created_at->format('d M, Y') }}</span>
                                            <small class="text-muted">{{ $item->created_at->format('h:i A') }}</small>
                                        </div>
                                    </td>
                                    <td class="text-center pe-4">
                                        <span class="text-success fw-bold">
                                            <i class="bi bi-check-circle-fill me-1"></i> Completed
                                        </span>
                                    </td>
                                </tr>
                                @if($item->comment)
                                <tr class="bg-light-subtle">
                                    <td colspan="5" class="ps-5 py-2 border-0">
                                        <div class="d-flex align-items-start text-muted bg-white p-2 rounded border border-dashed ms-4">
                                            <i class="bi bi-chat-left-dots me-2 mt-1"></i>
                                            <small class="italic">"{{ $item->comment }}"</small>
                                        </div>
                                    </td>
                                </tr>
                                @endif
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="bi bi-inbox fs-1 mb-3 d-block"></i>
                                            <p class="mb-0">No execution history found for this instance.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-0 py-3 text-end">
                    <small class="text-muted">Workflow initiated on {{ $instance->created_at->format('M d, Y') }}</small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-label-info {
        background-color: #e7f7ff !important;
        color: #03c3ec !important;
    }
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
