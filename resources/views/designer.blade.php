@extends('layouts.app')

@section('css')
<!-- LogicFlow CSS -->
<link rel="stylesheet" href="{{ asset('vendor/logicflow/core.css') }}" />
<link rel="stylesheet" href="{{ asset('vendor/logicflow/extension.css') }}" />
<link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons.css') }}">
<!-- Designer Custom Styling -->
<link rel="stylesheet" href="{{ asset('vendor/logicflow/designer.css') }}" />
@endsection

@section('content')
<div class="container-fluid py-4" id="designer_layout">
    <div class="d-flex justify-content-between align-items-center mb-4 px-2">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('workflow.processes.index') }}" class="btn btn-outline-secondary btn-sm rounded-circle shadow-sm d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" title="Back to Processes">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <p class="text-muted small mb-0">Workflow Designer</p>
                <h5 class="fw-bold text-primary mb-0">{{ $process->name }}</h5>
            </div>
        </div>
        <div class="d-flex gap-2">
            <!-- Zoom Controls -->
            <div class="btn-group bg-white shadow-sm" role="group" aria-label="Zoom controls">
                <button type="button" class="btn btn-outline-secondary btn-sm px-3" onclick="lf.zoom(false)" title="Zoom Out">
                    <i class="ri ri-subtract-line"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm px-3" onclick="lf.resetZoom()" title="Reset Zoom">
                    <i class="ri ri-aspect-ratio-line"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm px-3" onclick="lf.zoom(true)" title="Zoom In">
                    <i class="ri ri-add-line"></i>
                </button>                
            </div>
            
            <div class="btn-group shadow-sm" role="group" aria-label="JSON operations">
                <button type="button" class="btn btn-outline-primary btn-sm px-3" onclick="exportWorkflowAsJSON()" title="Export as JSON">
                    <i class="bi bi-download me-1"></i> Export JSON
                </button>
                <button type="button" class="btn btn-outline-primary btn-sm px-3" onclick="importWorkflowFromJSON()" title="Import from JSON">
                    <i class="bi bi-upload me-1"></i> Import JSON
                </button>
                <input type="file" id="import_workflow_file" accept=".json" style="display: none;" onchange="handleWorkflowFileImport(event)">
            </div>
            
            <button class="btn btn-primary btn-designer btn-sm shadow-sm" onclick="saveWorkflow()">
                <i class="bi bi-cloud-check me-1"></i> Save Workflow
            </button>
            <button type="button" class="btn btn-info btn-sm px-3" data-bs-toggle="modal" data-bs-target="#instructionsModal" title="How to Use">
                <i class="bi bi-question-circle"></i> &nbsp; Help
            </button>
        </div>
    </div>

    <div class="row g-4 position-relative">
        <!-- Sidebar -->
        <div class="col-md-2">
            <div class="card border-0 shadow-sm rounded-4 bg-light bg-opacity-50">
                <div class="card-body p-3 text-center">
                    <h6 class="fw-bold small text-uppercase text-muted mb-3">Toolbox</h6>
                    
                    <div class="toolbox-item" onmousedown="startDrag(event, 'step')">
                        <div class="tool-icon bg-primary bg-opacity-10 text-primary rounded-3">
                            <i class="bi bi-square"></i>
                        </div>
                        <span>Step</span>
                    </div>

                    <div class="toolbox-item" onmousedown="startDrag(event, 'condition')">
                        <div class="tool-icon bg-warning bg-opacity-10 text-warning rounded-3">
                            <div class="diamond-icon"></div>
                        </div>
                        <span>Condition</span>
                    </div>

                    <div class="toolbox-item" onmousedown="startDrag(event, 'start')">
                        <div class="tool-icon bg-success bg-opacity-10 text-success rounded-3">
                            <i class="ri ri-play-circle"></i>
                        </div>
                        <span>Start</span>
                    </div>

                    <div class="toolbox-item" onmousedown="startDrag(event, 'end')">
                        <div class="tool-icon bg-danger bg-opacity-10 text-danger rounded-3">
                            <i class="ri ri-stop-circle"></i>
                        </div>
                        <span>End</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Canvas -->
        <div id="canvas-col" class="col-md-10">
            <div id="workflow-container">
                <div id="lf-container"></div>

                <!-- Floating node action tooltip -->
                <div id="node-action-tooltip">
                    <div class="btn-group shadow-sm">
                        <button type="button" class="btn btn-sm btn-light border" id="node-action-edit" onclick="nodeActionEdit()">
                            <i class="bi bi-pencil-fill text-primary"></i> Edit
                        </button>
                        <button type="button" class="btn btn-sm btn-light border" id="node-action-delete" onclick="nodeActionDelete()">
                            <i class="bi bi-trash-fill text-danger"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Instructions Modal -->
<div class="modal fade" id="instructionsModal" tabindex="-1" aria-labelledby="instructionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="instructionsModalLabel"><i class="bi bi-info-circle text-primary me-2"></i>How to Use Workflow Designer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-muted">
                    <ul class="list-group list-group-flush mb-0">
                        <li class="list-group-item border-0 px-0 py-2">
                            <i class="bi bi-plus-square text-success me-2"></i>
                            <strong>Create Step:</strong> Drag & drop a shape from the Toolbox onto the canvas.
                        </li>
                        <li class="list-group-item border-0 px-0 py-2">
                            <i class="bi bi-arrows-move text-primary me-2"></i>
                            <strong>Move Step:</strong> Click and hold any step to drag it around the canvas.
                        </li>
                        <li class="list-group-item border-0 px-0 py-2">
                            <i class="bi bi-aspect-ratio text-info me-2"></i>
                            <strong>Resize Step:</strong> <strong>Click</strong> on a step to select it — a blue dotted border will appear with square handles at the corners and edges. Drag any handle to resize the node.
                        </li>
                        <li class="list-group-item border-0 px-0 py-2">
                            <i class="bi bi-diagram-3 text-warning me-2"></i>
                            <strong>Add Transition:</strong> Hover over a step to reveal blue anchor points. Click and drag an anchor to another step to connect them.
                        </li>
                        <li class="list-group-item border-0 px-0 py-2">
                            <i class="bi bi-sliders text-danger me-2"></i>
                            <strong>Edit Properties:</strong> Click once on any step or transition line to open the properties sidebar.
                        </li>
                    </ul>
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0">
                <button type="button" class="btn btn-primary px-4 rounded-pill" data-bs-dismiss="modal">Got it!</button>
            </div>
        </div>
    </div>
</div>

<!-- Right Side Properties Offcanvas -->
<div class="offcanvas offcanvas-end shadow" tabindex="-1" id="propertiesOffcanvas" aria-labelledby="propertiesOffcanvasLabel" style="width: 350px;">
    <div class="offcanvas-header border-bottom bg-light">
        <h6 class="offcanvas-title fw-bold" id="propertiesOffcanvasLabel"><i class="bi bi-sliders me-2"></i>Properties</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close" onclick="closeSidebar()"></button>
    </div>
    
    <div class="offcanvas-body">
        <div id="step-properties" class="property-section d-none">
            <form id="nodeForm">
                <input type="hidden" id="prop_node_id">
                <div class="mb-3">
                    <label id="prop_name_label" class="form-label small fw-bold text-muted text-uppercase">Step Name</label>
                    <input type="text" id="prop_name" class="form-control form-control-sm rounded-3">
                </div>
                <div class="mb-3 d-none" id="prop_code_container">
                    <label class="form-label small fw-bold text-muted text-uppercase">Machine Code</label>
                    <input type="text" id="prop_code" class="form-control form-control-sm rounded-3">
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted text-uppercase">Description</label>
                    <textarea id="prop_description" class="form-control form-control-sm rounded-3" rows="3"></textarea>
                </div>
                <div class="mb-3" id="prop_condition_container" style="display: none;">
                    <label class="form-label small fw-bold text-muted text-uppercase d-flex justify-content-between align-items-center">
                        Logic Conditions
                        <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none fw-bold" onclick="openConditionBuilder()">
                            <i class="bi bi-pencil-square"></i> Edit Logic
                        </button>
                    </label>
                    <div id="condition-summary" class="small text-muted p-3 border rounded-3 bg-light" style="min-height: 40px; cursor: pointer; line-height: 1.4;" onclick="openConditionBuilder()">
                        No conditions set.
                    </div>
                    <input type="hidden" id="prop_condition">
                </div>
                <div class="mb-3" id="prop_view_container">
                    <label class="form-label small fw-bold text-muted text-uppercase">Workflow Action</label>
                    <select id="prop_view" class="form-select form-select-sm rounded-3">
                        <option value="" selected>Select Workflow Action for the step</option>
                        @foreach ($workflowActions as $bladeView => $data)
                            <option value="{{ $bladeView }}">{{ $data['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="row g-2 mb-3" id="prop_start_end_container">
                    <div class="col-6">
                        <div class="form-check form-switch small">
                            <input class="form-check-input" type="checkbox" id="prop_is_start">
                            <label class="form-check-label fw-bold">Start</label>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-check form-switch small">
                            <input class="form-check-input" type="checkbox" id="prop_is_end">
                            <label class="form-check-label fw-bold">End</label>
                        </div>
                    </div>
                </div>
                <div class="mb-3" id="prop_roles_container">
                    <label class="form-label small fw-bold text-muted text-uppercase">Roles</label>
                    <div class="border rounded-3 p-2 bg-light" style="max-height: 150px; overflow-y: auto;">
                        @foreach($roles as $role)
                            <div class="form-check small mb-1">
                                <input class="form-check-input role-checkbox" type="checkbox" value="{{ $role->id }}" id="role_{{ $role->id }}">
                                <label class="form-check-label" for="role_{{ $role->id }}">{{ $role->display_name ?? $role->name }}</label>
                            </div>
                        @endforeach
                    </div>
                </div>
                <button type="button" class="btn btn-primary btn-sm w-100 rounded-pill mt-2" onclick="updateNodeData()">
                    Apply Changes
                </button>
                <button type="button" id="btn_delete_node" class="btn btn-outline-danger btn-sm w-100 rounded-pill mt-2" onclick="deleteSelected()">
                    Delete Step
                </button>
            </form>
        </div>

        <div id="edge-properties" class="property-section d-none">
            <form id="edgeForm">
                <input type="hidden" id="prop_edge_id">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted text-uppercase">Action Label</label>
                    <input type="text" id="prop_edge_label" class="form-control form-control-sm rounded-3" placeholder="Approve">
                </div>
                <div class="form-check form-switch small mb-3">
                    <input class="form-check-input" type="checkbox" id="prop_edge_is_default">
                    <label class="form-check-label fw-bold">Default Fallback</label>
                </div>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted text-uppercase">Branch Type</label>
                    <select id="prop_edge_branch_type" class="form-select form-select-sm rounded-3" onchange="autoUpdateEdgeLabel()">
                        <option value="DEFAULT">Default</option>
                        <option value="TRUE">True Branch</option>
                        <option value="FALSE">False Branch</option>
                    </select>
                </div>
                <button type="button" class="btn btn-primary btn-sm w-100 rounded-pill mt-2" onclick="updateEdgeData()">
                    Save Transition
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm w-100 rounded-pill mt-2" onclick="deleteSelected()">
                    Delete Transition
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Condition Builder Modal -->
<div class="modal fade" id="conditionBuilderModal" tabindex="-1" aria-labelledby="conditionBuilderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <form id="conditionForm" name="conditionForm" onsubmit="saveConditionFromBuilder(); return false;">
                @csrf
                <div class="modal-header border-bottom bg-light">
                    <h5 class="modal-title fw-bold" id="conditionBuilderModalLabel">
                        <i class="bi bi-diagram-3 text-primary me-2"></i>Condition Logic Builder
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body bg-light bg-opacity-50" style="max-height: 60vh; overflow-y: auto;">
                    <div id="builder-container">
                        <!-- ConditionBuilder JS will render here -->
                    </div>
                </div>
                <div class="modal-footer border-top bg-white">
                    <div class="me-auto small text-muted">
                        <i class="bi bi-info-circle"></i> Conditions determine which branch the workflow takes.
                    </div>
                    <button type="button" class="btn btn-outline-secondary px-4 rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 rounded-pill" >Save Logic</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('javascripts')
<!-- LogicFlow Core and Extensions -->
<script src="{{ asset('vendor/logicflow/logic-flow.js') }}"></script>
<script src="{{ asset('vendor/logicflow/Control.js') }}"></script>
<script src="{{ asset('vendor/logicflow/DndPanel.js') }}"></script>
<script src="{{ asset('vendor/logicflow/Menu.js') }}"></script>
<script src="{{ asset('vendor/logicflow/SelectionSelect.js') }}"></script>
<script>
    const workflowConfig = {
        processId: "{{ $process->id }}",
        roleMap: @json($roles->mapWithKeys(fn($role) => [$role->id => $role->display_name ?? $role->name])),
        csrfToken: "{{ csrf_token() }}",
        routes: {
            conditionsFields: "{{ route('workflow-conditions.fields') }}",
            stepsHasOpenTasks: "{{ route('workflow-designer.steps.has-open-tasks', ':stepId') }}",
            save: "{{ route('workflow-designer.save') }}",
            load: "{{ route('workflow-designer.load', $process->id) }}"
        }
    };
</script>
<!-- Load designer.js from vendor/logicflow -->
<script src="{{ asset('vendor/logicflow/designer.js') }}"></script>
@endsection
