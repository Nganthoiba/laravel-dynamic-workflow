@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-lg rounded-4">
                <div class="card-header bg-white py-4 border-bottom-0">
                    <div class="d-flex align-items-center">
                        <div class="avatar-md bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                            <i class="bi bi-layers-fill fs-3"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-bold">Create New Step</h4>
                            <p class="text-muted mb-0">Define a single operational node in your process</p>
                        </div>
                    </div>
                </div>
                <div class="card-body px-4 pb-4">
                    <form method="POST" action="{{ route('workflow.steps.store') }}">
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

                            <div class="col-md-8">
                                <label for="name" class="form-label fw-bold small text-uppercase text-secondary">Step Name</label>
                                <input type="text" name="name" id="name" class="form-control rounded-3" placeholder="e.g. Compliance Review" required>
                            </div>

                            <div class="col-md-4">
                                <label for="code" class="form-label fw-bold small text-uppercase text-secondary">Step Code</label>
                                <input type="text" name="code" id="code" class="form-control rounded-3" placeholder="COMPLIANCE_REV" required>
                            </div>

                            <div class="col-md-8">
                                <label for="workflow_action" class="form-label fw-bold small text-uppercase text-secondary">Workflow Action (UI View)</label>
                                <select name="workflow_action" id="workflow_action" class="form-select rounded-3" required>
                                    <option value="" selected disabled>Select Action...</option>
                                    @foreach(config('workflow.workflow_actions') as $key => $data)
                                        <option value="{{ $key }}">{{ is_array($data) ? $data['label'] : $data }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label for="stage_level" class="form-label fw-bold small text-uppercase text-secondary">Stage Level</label>
                                <input type="number" name="stage_level" id="stage_level" class="form-control rounded-3" value="1">
                            </div>

                            <div class="col-md-6">
                                <div class="form-check form-switch border p-2 rounded-3">
                                    <input class="form-check-input ms-0 me-2" type="checkbox" name="is_start" id="is_start" value="1">
                                    <label class="form-check-label fw-bold" for="is_start">Is Entry Point (Start)</label>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-check form-switch border p-2 rounded-3">
                                    <input class="form-check-input ms-0 me-2" type="checkbox" name="is_end" id="is_end" value="1">
                                    <label class="form-check-label fw-bold" for="is_end">Is Exit Point (End)</label>
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold small text-uppercase text-secondary">Authorized Roles</label>
                                <div class="d-flex flex-wrap gap-3 p-3 bg-light rounded-3 border">
                                    @foreach($roles as $id => $name)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $id }}" id="role_{{ $id }}">
                                            <label class="form-check-label" for="role_{{ $id }}">{{ $name }}</label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <div class="col-12 d-flex justify-content-between align-items-center mt-5 border-top pt-4">
                                <a href="{{ route('workflow.steps.index') }}" class="btn btn-link text-decoration-none text-secondary fw-bold px-0">
                                    <i class="bi bi-arrow-left me-1"></i> Back to List
                                </a>
                                <button type="submit" class="btn btn-primary px-5 py-2 rounded-pill fw-bold shadow-sm">
                                    Create Step <i class="bi bi-check-lg ms-1 small"></i>
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
