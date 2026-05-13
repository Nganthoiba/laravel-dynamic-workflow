@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Step Details</h5>
                    <div class="d-flex gap-2">
                        <a href="{{ route('workflow.steps.edit', $step) }}" class="btn btn-light btn-sm rounded-pill px-3 fw-bold">
                            <i class="bi bi-pencil me-1"></i> Edit
                        </a>
                        <a href="{{ route('workflow.steps.index') }}" class="btn btn-light btn-sm rounded-pill px-3 fw-bold">
                            <i class="bi bi-arrow-left me-1"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0 align-middle">
                        <tbody>
                            <tr>
                                <th class="ps-4 bg-light text-muted small text-uppercase" style="width: 30%;">Step Name</th>
                                <td class="ps-3 fw-bold text-dark fs-5">{{ $step->name }}</td>
                            </tr>
                            <tr>
                                <th class="ps-4 bg-light text-muted small text-uppercase">Code</th>
                                <td class="ps-3"><code class="text-primary fs-6">{{ $step->code }}</code></td>
                            </tr>
                            <tr>
                                <th class="ps-4 bg-light text-muted small text-uppercase">Process</th>
                                <td class="ps-3">{{ $step->process->name }}</td>
                            </tr>
                            <tr>
                                <th class="ps-4 bg-light text-muted small text-uppercase">Workflow Action</th>
                                <td class="ps-3"><span class="badge bg-light text-dark border rounded-pill">{{ $step->workflow_action }}</span></td>
                            </tr>
                            <tr>
                                <th class="ps-4 bg-light text-muted small text-uppercase">Stage Level</th>
                                <td class="ps-3">{{ $step->stage_level ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th class="ps-4 bg-light text-muted small text-uppercase">Flags</th>
                                <td class="ps-3">
                                    @if($step->is_start) <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-1 small me-1">Entry Point (Start)</span> @endif
                                    @if($step->is_end) <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-1 small me-1">Exit Point (End)</span> @endif
                                    @if($step->is_active) <span class="badge bg-info bg-opacity-10 text-info rounded-pill px-2 py-1 small me-1">Active</span> @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white py-4 px-4 border-top">
                    <h6 class="fw-bold text-muted small text-uppercase mb-3">Authorized Roles</h6>
                    <div class="d-flex flex-wrap gap-2">
                        @forelse($step->roles as $role)
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-medium">
                                {{ $role->display_name ?? $role->name }}
                            </span>
                        @empty
                            <span class="text-muted italic small">No specific roles assigned. Accessible by anyone with permission.</span>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
