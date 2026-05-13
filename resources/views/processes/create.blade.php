@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endsection

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card border-0 shadow-lg rounded-4">
                <div class="card-header bg-white py-4 border-bottom-0">
                    <div class="d-flex align-items-center">
                        <div class="avatar-md bg-primary bg-opacity-10 text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                            <i class="bi bi-plus-circle-fill fs-3"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-bold">Create New Process</h4>
                            <p class="text-muted mb-0">Define the core properties of your workflow process</p>
                        </div>
                    </div>
                </div>
                <div class="card-body px-4 pb-4">
                    <form method="POST" action="{{ route('workflow.processes.store') }}">
                        @csrf

                        <div class="row g-4">
                            <div class="col-md-8">
                                <label for="name" class="form-label fw-bold small text-uppercase text-secondary">Process Name</label>
                                <input type="text" 
                                       name="name" 
                                       id="name" 
                                       class="form-control form-control-sm rounded-3 @error('name') is-invalid @enderror" 
                                       placeholder="e.g. License Approval Pipeline"
                                       value="{{ old('name') }}"
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-4">
                                <label for="code" class="form-label fw-bold small text-uppercase text-secondary">Machine Code</label>
                                <input type="text" 
                                       name="code" 
                                       id="code" 
                                       class="form-control form-control-sm rounded-3 @error('code') is-invalid @enderror" 
                                       placeholder="LICENSE_APP"
                                       value="{{ old('code') }}"
                                       required>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label for="description" class="form-label fw-bold small text-uppercase text-secondary">Description</label>
                                <textarea name="description" 
                                          id="description" 
                                          rows="4" 
                                          class="form-control rounded-3" 
                                          placeholder="Describe the purpose and scope of this workflow...">{{ old('description') }}</textarea>
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch bg-light p-3 rounded-3 border">
                                    <input class="form-check-input ms-0 me-3" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold text-dark" for="is_active">
                                        Activate Process
                                        <small class="d-block text-muted fw-normal mt-1">Inactive processes cannot be used to start new workflow instances.</small>
                                    </label>
                                </div>
                            </div>

                            <div class="col-12 d-flex justify-content-between align-items-center mt-5 border-top pt-4">
                                <a href="{{ route('workflow.processes.index') }}" class="btn btn-link btn-sm text-decoration-none text-secondary fw-bold px-0">
                                    <i class="bi bi-arrow-left me-1"></i> Back to List
                                </a>
                                <button type="submit" class="btn btn-primary btn-sm px-5 py-2 rounded-pill fw-bold shadow-sm">
                                    Create Process <i class="bi bi-chevron-right ms-1 small"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Auto-generate code from name
    document.getElementById('name').addEventListener('input', function() {
        const nameValue = this.value;
        const codeInput = document.getElementById('code');
        if (!codeInput.dataset.edited) {
            codeInput.value = nameValue
                .toUpperCase()
                .replace(/[^A-Z0-9]/g, '_')
                .replace(/_+/g, '_')
                .replace(/^_|_$/g, '');
        }
    });

    document.getElementById('code').addEventListener('input', function() {
        this.dataset.edited = true;
    });
</script>
@endsection
