@extends('vendor.workflow.task_layout')
<!-- Every workflow step must extend this resources/views/workflow/task_layout.blade.php layout -->

@section('form_actions')
    <button type="submit" name="action_result" value="rejected" class="btn btn-outline-danger px-4">
        <i class="bi bi-x-circle me-1"></i> Reject
    </button>
    <button type="submit" name="action_result" value="approved" class="btn btn-primary px-5">
        <i class="bi bi-check2-circle me-1"></i> Approve & Forward
    </button>
@endsection
