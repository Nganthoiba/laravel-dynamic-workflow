@extends('layouts.app')

@section('css')
<!-- LogicFlow CSS -->
<link rel="stylesheet" href="{{ asset('vendor/logicflow/core.css') }}" />
<link rel="stylesheet" href="{{ asset('vendor/logicflow/extension.css') }}" />
<link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons.css') }}">
<style>
    :root {
        --lf-primary: #3b82f6;
        --lf-success: #10b981;
        --lf-danger: #ef4444;
        --lf-warning: #f59e0b;
        --sidebar-width: 300px;
    }

    ellipse {
        border: 1px solid #999 !important;
    }

    #workflow-container {
        height: calc(100vh - 160px);
        background: #fdfdfd;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        position: relative;
        overflow: hidden;
    }

    #lf-container {
        width: 100%;
        height: 100%;
        min-height: 600px;
    }

    /* LogicFlow overrides for Lucidchart look */
    .lf-node-step rect { fill: #fff; stroke: var(--lf-primary); stroke-width: 2px; }
    .lf-node-start ellipse { fill: #fff; stroke: var(--lf-success); stroke-width: 2px; }
    .lf-node-end ellipse { fill: #fff; stroke: var(--lf-danger); stroke-width: 2px; }
    .lf-node-condition polygon { fill: #fffbeb; stroke: var(--lf-warning); stroke-width: 2px; }

    /* Dotted rectangular guide when node is selected (for resizing) */
    .lf-resize-control rect,
    .lf-outline rect {
        stroke-dasharray: 5, 3 !important;
        stroke: var(--lf-primary) !important;
        stroke-width: 1.5px !important;
        fill: none !important;
        rx: 4px !important;
    }

    /* Resize handle dots — visible square anchors at corners/edges */
    .lf-resize-control > g {
        cursor: nwse-resize !important;
    }
    .lf-resize-control polygon,
    .lf-resize-control rect:not(:first-child) {
        fill: var(--lf-primary) !important;
        stroke: #fff !important;
        stroke-width: 1.5px !important;
    }

    /* Subtle hover ring on node shapes so user knows they can click */
    .lf-node-step:hover rect,
    .lf-node-start:hover ellipse,
    .lf-node-end:hover ellipse {
        filter: drop-shadow(0 0 4px rgba(59, 130, 246, 0.5));
        cursor: move !important;
    }

    /* Drag cursor for all node elements inside LogicFlow */
    .lf-node, .lf-node * {
        cursor: move !important;
    }
    .lf-node:active, .lf-node *:active {
        cursor: grabbing !important;
    }

    /* Floating node action tooltip (Edit / Delete) */
    #node-action-tooltip {
        position: absolute;
        z-index: 2000;
        display: none;
        pointer-events: auto;
        transform: translateX(-50%);
        transition: opacity 0.15s ease;
    }

    #node-action-tooltip .btn-group .btn {
        font-size: 0.75rem;
        padding: 3px 10px;
        border-radius: 0;
        box-shadow: none;
    }

    #node-action-tooltip .btn-group .btn:first-child { border-radius: 4px 0 0 4px; }
    #node-action-tooltip .btn-group .btn:last-child  { border-radius: 0 4px 4px 0; }
    .panel-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
        padding-bottom: 12px;
        border-bottom: 1px solid #f3f4f6;
    }

    /* Toolbox */
    .toolbox-item {
        padding: 12px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        margin-bottom: 12px;
        cursor: grab;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
        font-size: 0.8rem;
        font-weight: 500;
        color: #374151;
    }

    .toolbox-item:hover {
        border-color: var(--lf-primary);
        background: #f0f9ff;
        transform: translateY(-2px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .tool-icon {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .diamond-icon { width: 18px; height: 18px; border: 2px solid currentColor; transform: rotate(45deg); }
</style>
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
    const process_id = "{{ $process->id }}";
    // Initialize role mapping for canvas visualization
    const roleMap = {!! json_encode($roles->mapWithKeys(fn($role) => [$role->id => $role->display_name ?? $role->name])) !!};
    const { LogicFlow } = window;
    const Extension = window.LogicFlowExtension || window;
    
    const DndPanel = Extension.DndPanel;
    const SelectionSelect = Extension.SelectionSelect;
    const Menu = Extension.Menu || Extension.ContextMenu;
    const Control = Extension.Control;

    let lf;
    let conditionFields = [];
    let currentConditionData = { AND: [] };

    async function fetchConditionFields() {
        try {
            const resp = await fetch("{{ route('workflow-conditions.fields') }}");
            conditionFields = await resp.json();
        } catch (e) {
            console.error('Failed to fetch condition fields:', e);
        }
    }

    function renderConditionBuilder() {
        const container = document.getElementById('builder-container');
        container.innerHTML = '';
        
        const listDiv = document.createElement('div');
        listDiv.className = 'd-flex flex-column gap-2 mb-3';
        
        let items = currentConditionData.AND || [];
        if (!Array.isArray(items)) items = [];

        if (items.length === 0) {
            listDiv.innerHTML = '<div class="text-muted small p-3 text-center border rounded border-dashed">No rules defined. Workflow will always pass if empty.</div>';
        }

        items.forEach((item, index) => {
            const row = document.createElement('div');
            row.className = 'd-flex align-items-center gap-2 p-2 bg-white border rounded shadow-sm';
            
            if (index > 0) {
                const andBadge = document.createElement('span');
                andBadge.className = 'badge bg-secondary';
                andBadge.textContent = 'AND';
                row.appendChild(andBadge);
            } else {
                const whereBadge = document.createElement('span');
                whereBadge.className = 'badge bg-primary';
                whereBadge.textContent = 'WHERE';
                row.appendChild(whereBadge);
            }

            const fieldSelect = document.createElement('select');
            fieldSelect.className = 'form-select form-select-sm w-auto';
            conditionFields.forEach(f => {
                const opt = document.createElement('option');
                opt.value = f.key;
                opt.textContent = f.label;
                if (f.key === item.field) opt.selected = true;
                fieldSelect.appendChild(opt);
            });

            const opSelect = document.createElement('select');
            opSelect.className = 'form-select form-select-sm w-auto';
            opSelect.setAttribute("required", "true");
            
            const updateOpsAndValue = () => {
                const fieldDef = conditionFields.find(f => f.key === fieldSelect.value) || conditionFields[0];
                if (!fieldDef) return;

                const ops = fieldDef.operators_json || ['=', '!=', '>', '<', '>=', '<='];
                opSelect.innerHTML = '';
                ops.forEach(op => {
                    const opt = document.createElement('option');
                    opt.value = op;
                    const labels = {'=': 'is exactly', '!=': 'is not', '>': 'is greater than', '<': 'is less than', '>=': 'is >=', '<=': 'is <=', 'in': 'is one of', 'contains': 'contains'};
                    opt.textContent = labels[op] || op;
                    if (op === item.operator) opt.selected = true;
                    opSelect.appendChild(opt);
                });
                
                if (!ops.includes(item.operator)) item.operator = ops[0];

                let valInput = row.querySelector('.val-input');
                if (valInput) valInput.remove();

                if (fieldDef.type === 'enum' && fieldDef.options_json) {
                    valInput = document.createElement('select');
                    valInput.className = 'form-select form-select-sm val-input flex-grow-1';                   
                    
                    const defaultOpt = document.createElement('option');
                    defaultOpt.value = "";
                    defaultOpt.textContent = "Select Option";
                    valInput.appendChild(defaultOpt);

                    fieldDef.options_json.forEach(o => {
                        const opt = document.createElement('option');
                        opt.value = o.value;
                        opt.textContent = o.label;
                        if (o.value == item.value) opt.selected = true;
                        valInput.appendChild(opt);
                    });
                } else {
                    valInput = document.createElement('input');
                    valInput.type = fieldDef.type === 'number' ? 'number' : (fieldDef.type === 'date' ? 'date' : 'text');
                    valInput.className = 'form-control form-control-sm val-input flex-grow-1';
                    valInput.value = item.value || '';
                    valInput.placeholder = 'Enter value...';
                }

                valInput.setAttribute("required", "true");
                valInput.onchange = (e) => { item.value = e.target.value; updateSummaryPreview(); };
                row.insertBefore(valInput, removeBtn);
            };

            const removeBtn = document.createElement('button');
            removeBtn.className = 'btn btn-sm btn-outline-danger border-0';
            removeBtn.innerHTML = '<i class="bi bi-x-lg"></i>';
            removeBtn.onclick = () => {
                items.splice(index, 1);
                renderConditionBuilder();
                updateSummaryPreview();
            };

            fieldSelect.onchange = (e) => {
                item.field = e.target.value;
                updateOpsAndValue();
                updateSummaryPreview();
            };

            opSelect.onchange = (e) => {
                item.operator = e.target.value;
                updateSummaryPreview();
            };

            row.appendChild(fieldSelect);
            row.appendChild(opSelect);
            row.appendChild(removeBtn);
            
            listDiv.appendChild(row);
            updateOpsAndValue();
        });

        container.appendChild(listDiv);

        const addBtn = document.createElement('button');
        addBtn.className = 'btn btn-sm btn-outline-primary rounded-pill px-3';
        addBtn.innerHTML = '<i class="bi bi-plus-lg"></i> Add Rule';
        addBtn.onclick = () => {
            if (!currentConditionData.AND) currentConditionData.AND = [];
            const defaultField = conditionFields[0];
            currentConditionData.AND.push({
                field: defaultField ? defaultField.key : '',
                operator: defaultField && defaultField.operators_json ? defaultField.operators_json[0] : '=',
                value: ''
            });
            renderConditionBuilder();
            updateSummaryPreview();
        };
        container.appendChild(addBtn);
    }

    function getFlatSummary(data) {
        if (!data || !data.AND || data.AND.length === 0) return 'No conditions set.';
        const parts = data.AND.map(item => {
            const field = conditionFields.find(f => f.key === item.field)?.label || item.field;
            const opLabels = {'=': 'is', '!=': 'is not', '>': '>', '<': '<', '>=': '>=', '<=': '<=', 'in': 'in', 'contains': 'contains'};
            return `[${field}] ${opLabels[item.operator] || item.operator} "${item.value}"`;
        });
        return parts.join(' AND ');
    }

    function updateSummaryPreview() {}
    
    function initLogicFlow() {
        try {
            lf = new LogicFlow({
                container: document.querySelector('#lf-container'),
                grid: true,
                edgeTextDraggable: true,
                edgeType: 'polyline',
                keyboard: { enabled: true },
                stopScrollGraph: false,
                stopZoomGraph: false,
            });

            // Register custom 'step' node with premium split-block UI
            lf.register('step', ({ RectNode, RectNodeModel, h }) => {
                class StepModel extends RectNodeModel {
                    initNodeData(data) {
                        super.initNodeData(data);
                        // Further increased width (240px) to handle exceptionally long labels without truncation
                        this.width = data.width || 240;
                        this.height = data.height || 90;
                        this.radius = 10;
                    }
                    getNodeStyle() {
                        const style = super.getNodeStyle();
                        style.stroke = '#3b82f6';
                        style.strokeWidth = 2;
                        return style;
                    }
                }

                class StepView extends RectNode {
                    getShape() {
                        const { model } = this.props;
                        const { x, y, width, height, radius, properties } = model;
                        const style = model.getNodeStyle();
                        
                        const headerHeight = height * 0.4;
                        const roles = properties.roles || [];
                        const roleNames = roles.map(id => roleMap[id] || id).join(', ') || 'No roles assigned';
                        const name = model.text.value || 'Step';

                        // Truncate limit increased to 40 to accommodate long organizational titles
                        const truncate = (str, maxLen) => str.length > maxLen ? str.substring(0, maxLen - 3) + '...' : str;

                        return h('g', {}, [
                            // Main container box
                            h('rect', {
                                ...style,
                                x: x - width / 2,
                                y: y - height / 2,
                                width,
                                height,
                                rx: radius,
                                ry: radius,
                                fill: '#ffffff'
                            }),
                            // Header background
                            h('path', {
                                d: `M ${x - width / 2} ${y - height / 2 + radius} 
                                    A ${radius} ${radius} 0 0 1 ${x - width / 2 + radius} ${y - height / 2}
                                    L ${x + width / 2 - radius} ${y - height / 2}
                                    A ${radius} ${radius} 0 0 1 ${x + width / 2} ${y - height / 2 + radius}
                                    L ${x + width / 2} ${y - height / 2 + headerHeight}
                                    L ${x - width / 2} ${y - height / 2 + headerHeight}
                                    Z`,
                                fill: '#eff6ff',
                                stroke: 'none'
                            }),
                            // Header-Body separator
                            h('line', {
                                x1: x - width / 2,
                                y1: y - height / 2 + headerHeight,
                                x2: x + width / 2,
                                y2: y - height / 2 + headerHeight,
                                stroke: '#3b82f6',
                                strokeWidth: 1
                            }),
                            // Step Name text (Limit increased to 40)
                            h('text', {
                                x: x,
                                y: y - height / 2 + headerHeight / 2 + 5,
                                textAnchor: 'middle',
                                fontSize: 12,
                                fill: '#1e40af',
                                style: 'font-weight: bold; pointer-events: none;'
                            }, truncate(name, 40)),
                            // "Assigned Roles" label
                            h('text', {
                                x: x,
                                y: y - height / 2 + headerHeight + 18,
                                textAnchor: 'middle',
                                fontSize: 8,
                                fill: '#9ca3af',
                                style: 'font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; pointer-events: none;'
                            }, 'Assigned Roles'),
                            // Role List (Limit increased to 45)
                            h('text', {
                                x: x,
                                y: y - height / 2 + headerHeight + 38,
                                textAnchor: 'middle',
                                fontSize: 10,
                                fill: '#4b5563',
                                style: 'font-weight: 500; pointer-events: none;'
                            }, truncate(roleNames, 45))
                        ]);
                    }

                    getText() {
                        return null; // Suppress default text rendering
                    }
                }
                return { view: StepView, model: StepModel };
            });

            lf.register('start', ({ EllipseNode, EllipseNodeModel }) => {
                class StartModel extends EllipseNodeModel {
                    initNodeData(data) {
                        super.initNodeData(data);
                        this.rx = data.rx || 60;
                        this.ry = data.ry || 30;
                    }
                    getTextStyle() {
                        const style = super.getTextStyle();
                        style.dominantBaseline = 'middle';
                        style.textAnchor = 'middle';
                        return style;
                    }
                }
                return { view: EllipseNode, model: StartModel };
            });

            lf.register('end', ({ EllipseNode, EllipseNodeModel }) => {
                class EndModel extends EllipseNodeModel {
                    initNodeData(data) {
                        super.initNodeData(data);
                        this.rx = data.rx || 60;
                        this.ry = data.ry || 30;
                    }
                    getTextStyle() {
                        const style = super.getTextStyle();
                        style.dominantBaseline = 'middle';
                        style.textAnchor = 'middle';
                        return style;
                    }
                }
                return { view: EllipseNode, model: EndModel };
            });

            lf.register('condition', ({ DiamondNode, DiamondNodeModel }) => {
                class ConditionModel extends DiamondNodeModel {
                    initNodeData(data) {
                        super.initNodeData(data);
                        this.rx = data.rx || 50;
                        this.ry = data.ry || 50;
                    }
                    getTextStyle() {
                        const style = super.getTextStyle();
                        style.dominantBaseline = 'middle';
                        style.textAnchor = 'middle';
                        return style;
                    }
                }
                return { view: DiamondNode, model: ConditionModel };
            });

            lf.on('node:click', ({ data }) => {
                selectedElement = data;
                showResizeHandles(data.id);
                hideNodeTooltip();
            });

            lf.on('node:mouseenter', ({ data }) => {
                showNodeTooltip(data);
            });

            lf.on('node:mouseleave', ({ data }) => {
                tooltipHideTimer = setTimeout(() => hideNodeTooltip(), 300);
            });

            lf.on('edge:click', ({ data }) => {
                selectedElement = data;
                openSidebar();
                removeResizeHandles();
                hideNodeTooltip();
            });

            lf.on('blank:click', () => {
                selectedElement = null;
                lf.clearSelectElements();
                removeResizeHandles();
                hideNodeTooltip();
                closeSidebar();
            });

            lf.on('node:drag', ({ data }) => {
                if (isDraggingResize) return;
                hideNodeTooltip();
                if (selectedElement && selectedElement.id === data.id) {
                    renderHandles(lf.graphModel.getNodeModelById(data.id));
                }
            });

            lf.on('graph:transform', () => {
                hideNodeTooltip();
                if (selectedElement && !selectedElement.sourceNodeId) {
                    showResizeHandles(selectedElement.id);
                }
            });

            loadWorkflow();
            fetchConditionFields();
        } catch (e) {
            alert('Failed to initialize LogicFlow: ' + e.message);
            console.error('LogicFlow Init Error:', e);
        }
    }

    function startDrag(event, type) {
        if (!lf) {
            alert('LogicFlow is not initialized yet!');
            return;
        }
        try {
            lf.dnd.startDrag({
                type: type,
                text: type.charAt(0).toUpperCase() + type.slice(1)
            });
            event.preventDefault();
        } catch (e) {
            alert('Drag error: ' + e.message);
            console.error(e);
        }
    }

    let selectedElement = null;
    let tooltipHideTimer = null;
    let tooltipNodeData = null;

    function showNodeTooltip(data) {
        clearTimeout(tooltipHideTimer);
        tooltipNodeData = data;

        const tooltip = document.getElementById('node-action-tooltip');
        const lfContainer = document.getElementById('lf-container');
        if (!tooltip || !lfContainer) return;

        const model = lf.graphModel.getNodeModelById(data.id);
        if (!model) return;

        const transform = lf.graphModel.transformModel;
        const scale = transform.SCALE_X || 1;
        const isEllipse = model.type === 'start' || model.type === 'end';
        const hh = isEllipse ? (model.ry || 30) : (model.height || 70) / 2;

        const svgX = model.x * scale + transform.TRANSLATE_X;
        const svgY = (model.y - hh) * scale + transform.TRANSLATE_Y;

        const offsetX = lfContainer.offsetLeft || 0;
        const offsetY = lfContainer.offsetTop  || 0;

        tooltip.style.left = (offsetX + svgX) + 'px';
        tooltip.style.top  = (offsetY + svgY - 36) + 'px';
        tooltip.style.display = 'block';

        tooltip.onmouseenter = () => clearTimeout(tooltipHideTimer);
        tooltip.onmouseleave = () => { tooltipHideTimer = setTimeout(hideNodeTooltip, 200); };
    }

    function hideNodeTooltip() {
        const tooltip = document.getElementById('node-action-tooltip');
        if (tooltip) tooltip.style.display = 'none';
        tooltipNodeData = null;
    }

    function nodeActionEdit() {
        if (tooltipNodeData) {
            selectedElement = tooltipNodeData;
        }
        if (!selectedElement) return;
        openSidebar();
        hideNodeTooltip();
    }

    async function nodeActionDelete() {
        const target = tooltipNodeData || selectedElement;
        if (!target) return;
        hideNodeTooltip();
        removeResizeHandles();
        if (target.sourceNodeId) {
            lf.deleteEdge(target.id);
        } else {
            if (target.type === 'step') {
                try {
                    const checkUrl = "{{ route('workflow-designer.steps.has-open-tasks', ':stepId') }}".replace(':stepId', target.id);
                    const response = await fetch(checkUrl);
                    const result = await response.json();
                    if (result.has_open_tasks) {
                        if (!confirm('Warning: This step has active/open tasks. Deleting it may disrupt existing workflows. Are you sure you want to proceed?')) {
                            return;
                        }
                    }
                } catch (error) {
                    console.error('Error checking open tasks:', error);
                }
            }
            lf.deleteNode(target.id);
        }
        closeSidebar();
        selectedElement = null;
    }

    const HANDLE_SIZE = 9;
    let resizeSvgGroup = null;
    let resizeDrag = null;

    function getGraphContainer() {
        const lfSvg = document.querySelector('#lf-container svg');
        if (!lfSvg) return null;
        return lfSvg.querySelector('.lf-graph-data') ||
               lfSvg.querySelector('g[transform]') ||
               lfSvg.querySelector('g');
    }

    function removeResizeHandles() {
        if (resizeSvgGroup && resizeSvgGroup.parentNode) {
            resizeSvgGroup.parentNode.removeChild(resizeSvgGroup);
        }
        resizeSvgGroup = null;
    }

    function showResizeHandles(nodeId) {
        removeResizeHandles();
        const model = lf.graphModel.getNodeModelById(nodeId);
        if (!model) return;

        const container = getGraphContainer();
        if (!container) return;

        resizeSvgGroup = document.createElementNS('http://www.w3.org/2000/svg', 'g');
        resizeSvgGroup.setAttribute('class', 'custom-resize-group');
        resizeSvgGroup.setAttribute('data-node-id', nodeId);
        container.appendChild(resizeSvgGroup);
        renderHandles(model);
    }

    function renderHandles(model) {
        resizeSvgGroup.innerHTML = '';
        resizeSvgGroup.setAttribute('data-node-id', model.id);

        const { x, y } = model;
        const isEllipse = model.type === 'start' || model.type === 'end' || model.type === 'condition';
        const hw = isEllipse ? (model.rx || 60) : (model.width || 140) / 2;
        const hh = isEllipse ? (model.ry || 30) : (model.height || 70) / 2;

        const L = x - hw, R = x + hw, T = y - hh, B = y + hh, MX = x, MY = y;

        const border = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
        border.setAttribute('x', L - 4); border.setAttribute('y', T - 4);
        border.setAttribute('width', hw * 2 + 8); border.setAttribute('height', hh * 2 + 8);
        border.setAttribute('fill', 'none');
        border.setAttribute('stroke', '#3b82f6');
        border.setAttribute('stroke-width', '1.5');
        border.setAttribute('stroke-dasharray', '5,3');
        border.setAttribute('rx', '3');
        resizeSvgGroup.appendChild(border);

        const handles = [
            [L, T, 'nw-resize', 'nw'], [MX, T, 'n-resize', 'n'], [R, T, 'ne-resize', 'ne'],
            [R, MY, 'e-resize', 'e'],  [R, B, 'se-resize', 'se'], [MX, B, 's-resize', 's'],
            [L, B, 'sw-resize', 'sw'], [L, MY, 'w-resize', 'w'],
        ];

        handles.forEach(([hx, hy, cursor, pos]) => {
            const rect = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
            rect.setAttribute('x', hx - HANDLE_SIZE / 2);
            rect.setAttribute('y', hy - HANDLE_SIZE / 2);
            rect.setAttribute('width', HANDLE_SIZE);
            rect.setAttribute('height', HANDLE_SIZE);
            rect.setAttribute('fill', '#3b82f6');
            rect.setAttribute('stroke', '#fff');
            rect.setAttribute('stroke-width', '1.5');
            rect.setAttribute('rx', '1');
            rect.style.cursor = cursor;
            rect.setAttribute('data-handle', pos);
            rect.addEventListener('mousedown', startResizeDrag);
            resizeSvgGroup.appendChild(rect);
        });
    }

    let isDraggingResize = false;

    function startResizeDrag(e) {
        e.stopPropagation();
        e.preventDefault();
        const handle = e.target.getAttribute('data-handle');
        const nodeId = resizeSvgGroup.getAttribute('data-node-id');
        const model = lf.graphModel.getNodeModelById(nodeId);
        if (!model) return;
        const isEllipse = model.type === 'start' || model.type === 'end' || model.type === 'condition';

        const safeRx = (isFinite(model.rx) && model.rx > 0) ? model.rx : 60;
        const safeRy = (isFinite(model.ry) && model.ry > 0) ? model.ry : 30;
        const safeW  = (isFinite(model.width)  && model.width  > 0) ? model.width  : 140;
        const safeH  = (isFinite(model.height) && model.height > 0) ? model.height : 70;
        const safeX  = isFinite(model.x) ? model.x : 0;
        const safeY  = isFinite(model.y) ? model.y : 0;

        isDraggingResize = true;
        const lfNodes = document.querySelector('#lf-container svg .lf-nodes');
        if (lfNodes) lfNodes.style.pointerEvents = 'none';

        resizeDrag = {
            handle, nodeId, isEllipse,
            startMouseX: e.clientX,
            startMouseY: e.clientY,
            startW: isEllipse ? safeRx * 2 : safeW,
            startH: isEllipse ? safeRy * 2 : safeH,
            startX: safeX, startY: safeY,
        };
        document.addEventListener('mousemove', doResizeDrag);
        document.addEventListener('mouseup', endResizeDrag);
    }

    function doResizeDrag(e) {
        if (!resizeDrag) return;
        const { handle, nodeId, isEllipse, startMouseX, startMouseY, startW, startH, startX, startY } = resizeDrag;
        const model = lf.graphModel.getNodeModelById(nodeId);
        if (!model) return;

        const rawScale = lf.graphModel.transformModel ? lf.graphModel.transformModel.SCALE_X : 1;
        const scale = (isFinite(rawScale) && rawScale > 0) ? rawScale : 1;
        const dx = (e.clientX - startMouseX) / scale;
        const dy = (e.clientY - startMouseY) / scale;

        if (!isFinite(dx) || !isFinite(dy)) return;

        let newW = startW, newH = startH, newX = startX, newY = startY;
        const minW = 60, minH = 30;

        if (handle.includes('e')) { newW = Math.max(minW, startW + dx); newX = startX + (newW - startW) / 2; }
        if (handle.includes('w')) { newW = Math.max(minW, startW - dx); newX = startX - (newW - startW) / 2; }
        if (handle.includes('s')) { newH = Math.max(minH, startH + dy); newY = startY + (newH - startH) / 2; }
        if (handle.includes('n')) { newH = Math.max(minH, startH - dy); newY = startY - (newH - startH) / 2; }

        if (!isFinite(newW) || !isFinite(newH) || !isFinite(newX) || !isFinite(newY)) return;

        if (isEllipse) {
            model.rx = newW / 2; model.ry = newH / 2;
        } else {
            model.width = newW; model.height = newH;
        }
        model.x = newX; model.y = newY;

        if (model.text) {
            model.text.x = newX;
            model.text.y = newY;
        }

        renderHandles(model);
    }

    function endResizeDrag() {
        isDraggingResize = false;
        const lfNodes = document.querySelector('#lf-container svg .lf-nodes');
        if (lfNodes) lfNodes.style.pointerEvents = '';
        resizeDrag = null;
        document.removeEventListener('mousemove', doResizeDrag);
        document.removeEventListener('mouseup', endResizeDrag);
        if (resizeSvgGroup) {
            const nodeId = resizeSvgGroup.getAttribute('data-node-id');
            const model = lf.graphModel.getNodeModelById(nodeId);
            if (model) renderHandles(model);
        }
    }

    async function deleteSelected() {
        if (selectedElement) {
            if (selectedElement.sourceNodeId) {
                lf.deleteEdge(selectedElement.id);
            } else {
                if (selectedElement.type === 'step') {
                    try {
                        const checkUrl = "{{ route('workflow-designer.steps.has-open-tasks', ':stepId') }}".replace(':stepId', selectedElement.id);
                        const response = await fetch(checkUrl);
                        const result = await response.json();
                        if (result.has_open_tasks) {
                            if (!confirm('Warning: This step has active/open tasks. Deleting it may disrupt existing workflows. Are you sure you want to proceed?')) {
                                return;
                            }
                        }
                    } catch (error) {
                        console.error('Error checking open tasks:', error);
                    }
                }
                lf.deleteNode(selectedElement.id);
            }
            closeSidebar();
        }
    }

    let propertiesOffcanvas;
    
    document.addEventListener('DOMContentLoaded', function () {
        const offcanvasEl = document.getElementById('propertiesOffcanvas');
        if (offcanvasEl) {
            propertiesOffcanvas = new bootstrap.Offcanvas(offcanvasEl, { backdrop: false });
        }
    });

    function openSidebar() {
        if (!selectedElement) return;
        
        const nodeSection = document.getElementById('step-properties');
        const edgeSection = document.getElementById('edge-properties');
        
        nodeSection.classList.add('d-none');
        edgeSection.classList.add('d-none');

        if (selectedElement.sourceNodeId) {
            populateEdgeProperties(selectedElement);
            edgeSection.classList.remove('d-none');
        } else {
            populateNodeProperties(selectedElement);
            nodeSection.classList.remove('d-none');
        }
        
        if (propertiesOffcanvas) propertiesOffcanvas.show();
    }

    function closeSidebar() {
        if (propertiesOffcanvas) propertiesOffcanvas.hide();
    }

    function populateNodeProperties(data) {
        const nodeData = data.properties || {};
        document.getElementById('prop_node_id').value = data.id;
        document.getElementById('prop_name').value = data.text?.value || data.type;
        document.getElementById('prop_code').value = nodeData.code || '';
        document.getElementById('prop_description').value = nodeData.description || '';
        document.getElementById('prop_view').value = nodeData.view || '';
        document.getElementById('prop_is_start').checked = nodeData.is_start || data.type === 'start';
        document.getElementById('prop_is_end').checked = nodeData.is_end || data.type === 'end';
        
        const condContainer = document.getElementById('prop_condition_container');
        const viewContainer = document.getElementById('prop_view_container');
        const codeContainer = document.getElementById('prop_code_container');
        const startEndContainer = document.getElementById('prop_start_end_container');
        const rolesContainer = document.getElementById('prop_roles_container');
        const nameLabel = document.getElementById('prop_name_label');
        const deleteBtn = document.getElementById('btn_delete_node');
        
        if (data.type === 'condition') {
            nameLabel.innerText = 'Condition Name';
            if(deleteBtn) deleteBtn.innerText = 'Delete Condition';
            condContainer.style.display = 'block';
            viewContainer.style.display = 'none';
            codeContainer.style.display = 'none';
            startEndContainer.style.display = 'none';
            rolesContainer.style.display = 'none';
            
            const condData = typeof nodeData.condition === 'string' ? JSON.parse(nodeData.condition || '{}') : (nodeData.condition || {});
            document.getElementById('prop_condition').value = JSON.stringify(condData);
            updateConditionSummary(condData);
        } else {
            nameLabel.innerText = 'Step Name';
            if(deleteBtn) deleteBtn.innerText = 'Delete Step';
            condContainer.style.display = 'none';
            viewContainer.style.display = 'block';
            codeContainer.style.display = 'block';
            startEndContainer.style.display = 'flex';
            rolesContainer.style.display = 'block';
            document.getElementById('prop_condition').value = '';
        }

        document.querySelectorAll('.role-checkbox').forEach(cb => {
            cb.checked = nodeData.roles && nodeData.roles.includes(parseInt(cb.value));
        });
    }

    function populateEdgeProperties(data) {
        const edgeData = data.properties || {};
        document.getElementById('prop_edge_id').value = data.id;
        document.getElementById('prop_edge_label').value = data.text?.value || '';
        document.getElementById('prop_edge_is_default').checked = edgeData.is_default || false;
        document.getElementById('prop_edge_branch_type').value = edgeData.branch_type || 'DEFAULT';
    }

    function updateNodeData() {
        const id = document.getElementById('prop_node_id').value;
        const name = document.getElementById('prop_name').value;
        const code = document.getElementById('prop_code').value;
        const description = document.getElementById('prop_description').value;
        const view = document.getElementById('prop_view').value;
        const is_start = document.getElementById('prop_is_start').checked;
        const is_end = document.getElementById('prop_is_end').checked;
        const roles = Array.from(document.querySelectorAll('.role-checkbox:checked')).map(cb => parseInt(cb.value));
        
        const conditionRaw = document.getElementById('prop_condition').value;
        let condition = null;
        if (conditionRaw.trim()) {
            try { condition = JSON.parse(conditionRaw); } catch (e) { condition = conditionRaw; }
        }

        lf.updateText(id, name);
        lf.setProperties(id, { code, description, view, is_start, is_end, roles, condition });
        closeSidebar();
    }

    function autoUpdateEdgeLabel() {
        const branchType = document.getElementById('prop_edge_branch_type').value;
        const labelInput = document.getElementById('prop_edge_label');
        if (branchType === 'TRUE') {
            labelInput.value = 'True';
        } else if (branchType === 'FALSE') {
            labelInput.value = 'False';
        } else if (branchType === 'DEFAULT' && (labelInput.value === 'True' || labelInput.value === 'False')) {
            labelInput.value = '';
        }
    }

    function updateEdgeData() {
        const id = document.getElementById('prop_edge_id').value;
        const label = document.getElementById('prop_edge_label').value;
        const is_default = document.getElementById('prop_edge_is_default').checked;
        const branch_type = document.getElementById('prop_edge_branch_type').value;
        
        lf.updateText(id, label);
        lf.setProperties(id, { is_default, branch_type });
        closeSidebar();
    }

    function openConditionBuilder() {
        if (conditionFields.length === 0) {
            alert('Condition fields are not loaded yet.');
            return;
        }
        
        try {
            currentConditionData = JSON.parse(document.getElementById('prop_condition').value || '{"AND": []}');
        } catch (e) {
            currentConditionData = { AND: [] };
        }
        
        renderConditionBuilder();
        const modal = new bootstrap.Modal(document.getElementById('conditionBuilderModal'));
        modal.show();
    }

    function saveConditionFromBuilder() {
        document.getElementById('prop_condition').value = JSON.stringify(currentConditionData);
        updateConditionSummary(currentConditionData);
        
        const modalEl = document.getElementById('conditionBuilderModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        modal.hide();
        
        const nodeId = document.getElementById('prop_node_id').value;
        if (nodeId) {
            lf.setProperties(nodeId, { condition: currentConditionData });
        }
    }

    function updateConditionSummary(data) {
        const summaryEl = document.getElementById('condition-summary');
        const summary = getFlatSummary(data);
        summaryEl.innerHTML = summary === 'No conditions set.' 
            ? '<i class="bi bi-plus-circle"></i> Click to add logic conditions...' 
            : `<div class="fw-bold text-primary mb-1"><i class="bi bi-cpu"></i> Logic:</div><div class="font-monospace small">${summary}</div>`;
    }

    async function saveWorkflow() {
        const graphData = lf.getGraphData();
        const nodes = [];
        const edges = [];

        graphData.nodes.forEach((node, index) => {
            const nodeModel = lf.getNodeModelById(node.id);

            const isEllipse = node.type === 'start' || node.type === 'end' || node.type === 'condition';
            let currentWidth = 240, currentHeight = 90;

            if (nodeModel) {
                if (isEllipse) {
                    currentWidth  = (nodeModel.rx || 60) * 2;
                    currentHeight = (nodeModel.ry || 30) * 2;
                } else {
                    currentWidth  = nodeModel.width  || 140;
                    currentHeight = nodeModel.height || 60;
                }
            }

            const width  = parseFloat(currentWidth);
            const height = parseFloat(currentHeight);

            const newGraphNode = {
                ...node,
                width,
                height
            };
            
            if (isEllipse && nodeModel) {
                newGraphNode.rx = Math.round(nodeModel.rx);
                newGraphNode.ry = Math.round(nodeModel.ry);
            }
            
            graphData.nodes[index] = newGraphNode;

            nodes.push({
                id: node.id,
                node_type: node.type,
                name: node.text?.value || node.type,
                code: node.properties.code,
                description: node.properties.description,
                condition_json: node.properties.condition || null,
                workflow_action: node.properties.view,
                is_start: node.properties.is_start,
                is_end: node.properties.is_end,
                roles: node.properties.roles,
                ui_json: {
                    x: Math.round(node.x),
                    y: Math.round(node.y),
                    width:  width,
                    height: height,
                    rx: nodeModel && nodeModel.rx ? Math.round(nodeModel.rx) : null,
                    ry: nodeModel && nodeModel.ry ? Math.round(nodeModel.ry) : null,
                }
            });
        });

        graphData.edges.forEach(edge => {
            const sourceNode = graphData.nodes.find(n => n.id === edge.sourceNodeId);
            const targetNode = graphData.nodes.find(n => n.id === edge.targetNodeId);

            edges.push({
                id: edge.id,
                from: sourceNode.id,
                to: targetNode.id,
                label: edge.text?.value || 'Forward',
                is_default: edge.properties.is_default || false,
                branch_type: edge.properties.branch_type || 'DEFAULT'
            });
        });

        const startNodesCount = graphData.nodes.filter(n => n.type === 'start' || (n.properties && n.properties.is_start)).length;
        if (startNodesCount !== 1) {
            alert('Validation Error: The workflow must have exactly one start step.');
            return;
        }

        const leafNodes = graphData.nodes.filter(n => {
            return !graphData.edges.some(e => e.sourceNodeId === n.id);
        });

        for (const leaf of leafNodes) {
            if (leaf.type === 'condition') {
                alert('Validation Error: A condition (diamond) node cannot be a leaf node. It must have outgoing transitions.');
                return;
            }
            if (leaf.type !== 'step' && leaf.type !== 'end') {
                alert(`Validation Error: Leaf node "${leaf.text?.value || leaf.type}" must be a step or end node.`);
                return;
            }
        }

        const defaultTransitionsBySource = {};
        for (const edge of edges) {
            if (edge.is_default) {
                defaultTransitionsBySource[edge.from] = (defaultTransitionsBySource[edge.from] || 0) + 1;
                if (defaultTransitionsBySource[edge.from] > 1) {
                    const node = graphData.nodes.find(n => n.id === edge.from);
                    alert(`Validation Error: Step "${node?.text?.value || node?.type}" has more than one default transition.`);
                    return;
                }
            }
        }

        const conditionNodes = graphData.nodes.filter(n => n.type === 'condition');
        for (const cond of conditionNodes) {
            const outgoing = edges.filter(e => e.from === cond.id);
            if (outgoing.length !== 2) {
                alert(`Validation Error: Condition node "${cond.text?.value || 'Condition'}" must have exactly 2 outgoing branches (currently has ${outgoing.length}).`);
                return;
            }
            const hasTrue = outgoing.some(e => e.branch_type === 'TRUE');
            const hasFalse = outgoing.some(e => e.branch_type === 'FALSE');
            if (!hasTrue || !hasFalse) {
                alert(`Validation Error: Condition node "${cond.text?.value || 'Condition'}" must have exactly one TRUE branch and one FALSE branch.`);
                return;
            }
        }

        try {
            const response = await fetch("{{ route('workflow-designer.save') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    process_id: process_id,
                    nodes: nodes,
                    edges: edges,
                    drawflow_json: graphData
                })
            });
            const result = await response.json();
            if (result.status === 'success') {
                alert('Workflow saved successfully!');
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            alert('An error occurred while saving.');
        }
    }

    async function loadWorkflow() {
        try {
            const response = await fetch(`{{ route('workflow-designer.load', $process->id) }}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            
            if (!response.ok) {
                throw new Error('Server returned ' + response.status);
            }

            const data = await response.json();
            
            setTimeout(() => {
                try {
                    let validGraphJson = false;
                    let parsedGraph = null;
                    
                    if (data.graph_json) {
                        parsedGraph = typeof data.graph_json === 'string' ? JSON.parse(data.graph_json) : data.graph_json;
                        if (parsedGraph && parsedGraph.nodes && !parsedGraph.drawflow) {
                            validGraphJson = true;
                        }
                    }

                    if (validGraphJson) {
                        if (parsedGraph.nodes && data.nodes) {
                            parsedGraph.nodes.forEach(gn => {
                                const dbNode = data.nodes.find(n => n.temp_id === gn.id || n.id === gn.id);
                                if (dbNode) {
                                    gn.properties = gn.properties || {};
                                    gn.properties.db_id = dbNode.id;
                                    
                                    let uiJson = null;
                                    if (dbNode.ui_json) {
                                        try { uiJson = typeof dbNode.ui_json === 'string' ? JSON.parse(dbNode.ui_json) : dbNode.ui_json; } catch(e){}
                                    }
                                    if (uiJson) {
                                        if (!gn.rx && uiJson.rx) gn.rx = uiJson.rx;
                                        if (!gn.ry && uiJson.ry) gn.ry = uiJson.ry;
                                        if (!gn.width && uiJson.width) gn.width = uiJson.width;
                                        if (!gn.height && uiJson.height) gn.height = uiJson.height;
                                    }
                                }
                            });
                        }
                        lf.render(parsedGraph);
                        lf.translateCenter();
                        setTimeout(() => lf.fitView(), 100);
                    } else if (data.nodes && data.nodes.length > 0) {
                        const lfData = { nodes: [], edges: [] };
                        data.nodes.forEach(n => {
                            let uiJson = null;
                            if (n.ui_json) {
                                try { uiJson = typeof n.ui_json === 'string' ? JSON.parse(n.ui_json) : n.ui_json; } catch(e){}
                            }

                            const nodeType = n.node_type || (n.is_start ? 'start' : (n.is_end ? 'end' : 'step'));
                            const isEllipse = nodeType === 'start' || nodeType === 'end' || nodeType === 'condition';

                            const nodeData = {
                                id: n.id,
                                type: nodeType,
                                x: (uiJson && uiJson.x) ? Number(uiJson.x) : (Math.random() * 500 + 100),
                                y: (uiJson && uiJson.y) ? Number(uiJson.y) : (Math.random() * 500 + 100),
                                text: n.name || 'Step',
                                properties: {
                                    code: n.code,
                                    description: n.description,
                                    condition: n.condition_json,
                                    view: n.workflow_action,
                                    is_start: n.is_start,
                                    is_end: n.is_end,
                                    roles: n.roles
                                }
                            };

                            if (isEllipse) {
                                nodeData.rx = (uiJson && uiJson.rx > 0) ? uiJson.rx : (nodeType === 'condition' ? 50 : 60);
                                nodeData.ry = (uiJson && uiJson.ry > 0) ? uiJson.ry : (nodeType === 'condition' ? 50 : 30);
                            } else {
                                nodeData.width  = (uiJson && uiJson.width  > 0) ? uiJson.width  : 240;
                                nodeData.height = (uiJson && uiJson.height > 0) ? uiJson.height : 90;
                            }

                            lfData.nodes.push(nodeData);
                        });
                        
                        data.edges.forEach(e => {
                            const source = data.nodes.find(n => n.id === e.from);
                            const target = data.nodes.find(n => n.id === e.to);
                            
                            if (source && target) {
                                lfData.edges.push({
                                    id: e.id,
                                    sourceNodeId: source.id,
                                    targetNodeId: target.id,
                                    text: e.label || '',
                                    type: 'polyline',
                                    properties: {
                                        is_default: e.is_default,
                                        branch_type: e.branch_type
                                    }
                                });
                            }
                        });
                        lf.render(lfData);
                        lf.translateCenter();
                        setTimeout(() => lf.fitView(), 100);
                    } else {
                        lf.render({ nodes: [], edges: [] });
                    }
                } catch (renderError) {
                    alert('Rendering error: ' + renderError.message);
                }
            }, 300);
        } catch (error) {
            console.error('Error loading workflow:', error);
            alert('Load failed: ' + error.message);
        }
    }

    window.addEventListener('load', initLogicFlow);
</script>
@endsection
