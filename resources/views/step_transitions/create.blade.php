@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endsection

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-lg rounded-4">
                <div class="card-header bg-white py-4 border-bottom-0">
                    <div class="d-flex align-items-center">
                        <div class="avatar-md bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                            <i class="bi bi-shuffle fs-3"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-bold">Create Step Transition</h4>
                            <p class="text-muted mb-0">Define how the workflow moves from one step to another</p>
                        </div>
                    </div>
                </div>
                <div class="card-body px-4 pb-4">
                    <form method="POST" action="{{ route('workflow.step-transitions.store') }}">
                        @csrf

                        <div class="row g-4">
                            <div class="col-md-12">
                                <label for="process_id" class="form-label fw-bold small text-uppercase text-secondary">Workflow Process</label>
                                <select name="process_id" id="process_id" class="form-select rounded-3" required>
                                    <option value="" selected disabled>Select Process...</option>
                                    @foreach($processes as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="from_step_id" class="form-label fw-bold small text-uppercase text-secondary">From Step</label>
                                <select name="from_step_id" id="from_step_id" class="form-select rounded-3" required>
                                    <option value="" selected disabled>Select Step...</option>
                                    @foreach($steps as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="to_step_id" class="form-label fw-bold small text-uppercase text-secondary">To Step</label>
                                <select name="to_step_id" id="to_step_id" class="form-select rounded-3" required>
                                    <option value="" selected disabled>Select Step...</option>
                                    @foreach($steps as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-8">
                                <label for="action_label" class="form-label fw-bold small text-uppercase text-secondary">Action Label (Button Text)</label>
                                <input type="text" name="action_label" id="action_label" class="form-control rounded-3" placeholder="e.g. Approve & Forward" required>
                            </div>

                            <div class="col-md-4">
                                <label for="priority" class="form-label fw-bold small text-uppercase text-secondary">Priority</label>
                                <input type="number" name="priority" id="priority" class="form-control rounded-3" value="1">
                            </div>

                            <div class="col-12">
                                <label for="condition_json" class="form-label fw-bold small text-uppercase text-secondary">Condition Logic (JSON)</label>
                                <textarea name="condition_json" id="condition_json" class="form-control rounded-3" rows="3" placeholder='{"field": "total", "operator": ">", "value": 1000}'></textarea>
                                <small class="text-muted">Use the Workflow Designer for a more visual condition builder experience.</small>
                            </div>

                            <div class="col-md-6">
                                <div class="form-check form-switch border p-2 rounded-3">
                                    <input class="form-check-input ms-0 me-2" type="checkbox" name="is_default" id="is_default" value="1">
                                    <label class="form-check-label fw-bold" for="is_default">Default Transition</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-check form-switch border p-2 rounded-3">
                                    <input class="form-check-input ms-0 me-2" type="checkbox" name="is_active" id="is_active" value="1" checked>
                                    <label class="form-check-label fw-bold" for="is_active">Transition Active</label>
                                </div>
                            </div>

                            <div class="col-12 d-flex justify-content-between align-items-center mt-5 border-top pt-4">
                                <a href="{{ route('workflow.step-transitions.index') }}" class="btn btn-link text-decoration-none text-secondary fw-bold px-0">
                                    <i class="bi bi-arrow-left me-1"></i> Back to List
                                </a>
                                <button type="submit" class="btn btn-primary px-5 py-2 rounded-pill fw-bold shadow-sm">
                                    Create Transition <i class="bi bi-check-lg ms-1 small"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
