{{--
    View: Edit Binding Form
    
    Purpose:
    Renders a user-friendly form to edit an existing workflow binding.
    Allows changing the workflow process, model type, lifecycle event, trigger mechanism, priority, and activation state.
    
    Usage:
    Returned by WorkflowBindingController@edit, accessed via route('workflow-bindings.edit', $bindingId).
    Extends layouts.app and relies on Bootstrap 5 styling and validation states.
--}}

@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons.css') }}">
@endsection

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            
            <!-- Breadcrumbs / Back button -->
            <div class="mb-4">
                <a href="{{ route('workflow-bindings.index') }}" class="text-decoration-none text-secondary small fw-bold">
                    <i class="bi bi-arrow-left me-1"></i> Back to Workflow Bindings
                </a>
            </div>

            <!-- Form Card -->
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="card-header bg-white py-4 px-4 border-bottom-0">
                    <div class="d-flex align-items-center">
                        <div class="avatar-md bg-warning bg-opacity-10 text-warning rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                            <i class="bi bi-pencil-square fs-3"></i>
                        </div>
                        <div>
                            <h4 class="mb-0 fw-bold">Edit Binding Rule</h4>
                            <p class="text-muted mb-0 small">Update the trigger configuration for this workflow binding</p>
                        </div>
                    </div>
                </div>

                <div class="card-body px-4 pb-4">
                    <form method="POST" action="{{ route('workflow-bindings.update', $binding->id) }}">
                        @csrf
                        @method('PUT')

                        <!-- Validation Summary for specific custom errors -->
                        @if($errors->has('process_id') && count($errors->all()) > 0)
                            <div class="alert alert-danger border-0 rounded-3 small px-3 py-2 mb-4" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ $errors->first('process_id') }}
                            </div>
                        @endif

                        <div class="row g-4">
                            <!-- Process Dropdown -->
                            <div class="col-12">
                                <label for="process_id" class="form-label fw-bold small text-uppercase text-secondary">Workflow Process</label>
                                <select name="process_id" id="process_id" class="form-select form-select-sm rounded-3 @error('process_id') is-invalid @enderror" required>
                                    @foreach($processes as $process)
                                        <option value="{{ $process->id }}" {{ old('process_id', $binding->process_id) == $process->id ? 'selected' : '' }}>
                                            {{ $process->name }} ({{ $process->code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('process_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text text-muted small">Choose the workflow process template that will be instantiated.</div>
                            </div>

                            <!-- Model Dropdown -->
                            <div class="col-12 col-md-6">
                                <label for="model_type" class="form-label fw-bold small text-uppercase text-secondary">Business Model</label>
                                <select name="model_type" id="model_type" class="form-select form-select-sm rounded-3 @error('model_type') is-invalid @enderror" required>
                                    @foreach($models as $key => $label)
                                        <option value="{{ $key }}" {{ old('model_type', $currentModelKey) === $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('model_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text text-muted small">Choose the entity that triggers the workflow.</div>
                            </div>

                            <!-- Event Dropdown -->
                            <div class="col-12 col-md-6">
                                <label for="event_name" class="form-label fw-bold small text-uppercase text-secondary">Trigger Event</label>
                                <select name="event_name" id="event_name" class="form-select form-select-sm rounded-3 @error('event_name') is-invalid @enderror" required>
                                    @foreach($events as $key => $label)
                                        <option value="{{ $key }}" {{ old('event_name', $binding->event_name) === $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('event_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text text-muted small">Choose the lifecycle action triggering the workflow.</div>
                            </div>

                            <!-- Priority Selector -->
                            <div class="col-12 col-md-6">
                                <label for="priority" class="form-label fw-bold small text-uppercase text-secondary">Evaluation Priority</label>
                                <input type="number" 
                                       name="priority" 
                                       id="priority" 
                                       class="form-control form-control-sm rounded-3 @error('priority') is-invalid @enderror" 
                                       value="{{ old('priority', $binding->priority) }}"
                                       min="1" 
                                       required>
                                @error('priority')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text text-muted small">Used for conflict resolution. Higher values take precedence.</div>
                            </div>

                            <!-- Active Switch Toggle -->
                            <div class="col-12">
                                <div class="form-check form-switch bg-light p-3 rounded-3 border">
                                    <input class="form-check-input ms-0 me-3" 
                                           type="checkbox" 
                                           name="is_active" 
                                           id="is_active" 
                                           value="1" 
                                           {{ old('is_active', $binding->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold text-dark" for="is_active">
                                        Activate Binding Rule
                                        <small class="d-block text-muted fw-normal mt-1">If disabled, the workflow process will not trigger even if event occurs.</small>
                                    </label>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="col-12 d-flex justify-content-between align-items-center mt-5 border-top pt-4">
                                <a href="{{ route('workflow-bindings.index') }}" class="btn btn-link btn-sm text-decoration-none text-secondary fw-bold px-0">
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-primary btn-sm px-5 py-2 rounded-pill fw-bold shadow-sm">
                                    Update Binding Rule <i class="bi bi-check-lg ms-1"></i>
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
