<?php

namespace Workflow\Http\Controllers;

use Illuminate\Http\Request;
use Workflow\Models\Process;
use Workflow\Models\WorkflowBinding;
use Workflow\Services\WorkflowBindingRegistry;

/**
 * Controller: WorkflowBindingController
 *
 * Purpose:
 * Handles CRUD operations and status toggling for database-driven workflow bindings.
 * It manages the relations between processes and model trigger configurations, ensuring
 * that validation and conflict checks are performed prior to persistence.
 *
 * Usage:
 * Automatically routed via the package web routes group.
 * Accessible in routes using `route('workflow-bindings.index')`, etc.
 */
class WorkflowBindingController extends Controller
{
    protected WorkflowBindingRegistry $registry;

    /**
     * Create a new controller instance.
     */
    public function __construct(WorkflowBindingRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Display a listing of the workflow bindings.
     */
    public function index()
    {
        $bindings = WorkflowBinding::with('process')->latest()->paginate(10);

        return view('workflow::bindings.index', [
            'bindings' => $bindings,
            'registry' => $this->registry,
        ]);
    }

    /**
     * Show the form for creating a new workflow binding.
     */
    public function create()
    {
        $processes = Process::where('is_active', true)->orderBy('name')->get();
        $models = $this->registry->getModels();
        $events = $this->registry->getEvents();

        return view('workflow::bindings.create', [
            'processes' => $processes,
            'models'    => $models,
            'events'    => $events,
        ]);
    }

    /**
     * Store a newly created workflow binding in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'process_id'   => 'required|exists:processes,id',
            'model_type'   => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!$this->registry->isModelAllowed($value)) {
                        $fail('The selected model type is invalid.');
                    }
                },
            ],
            'event_name'   => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!$this->registry->isEventAllowed($value)) {
                        $fail('The selected event is invalid.');
                    }
                },
            ],
            'priority'     => 'required|integer|min:1',
        ]);

        // Resolve the key to actual FQCN class name
        $modelClass = $this->registry->getModelClass($request->model_type) ?? $request->model_type;

        // Check for duplicates
        $duplicate = WorkflowBinding::where('process_id', $request->process_id)
            ->where('model_type', $modelClass)
            ->where('event_name', $request->event_name)
            ->exists();

        if ($duplicate) {
            return back()
                ->withErrors(['process_id' => 'A binding for this process, model, and event combination already exists.'])
                ->withInput();
        }

        WorkflowBinding::create([
            'process_id'   => $request->process_id,
            'model_type'   => $modelClass,
            'event_name'   => $request->event_name,
            'priority'     => $request->priority,
            'is_active'    => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('workflow-bindings.index')
            ->with('success', 'Workflow binding created successfully.');
    }

    /**
     * Show the form for editing the specified workflow binding.
     */
    public function edit($id)
    {
        $binding = WorkflowBinding::findOrFail($id);
        $processes = Process::where('is_active', true)->orderBy('name')->get();
        $models = $this->registry->getModels();
        $events = $this->registry->getEvents();

        // Resolve class name to registry key for selection mapping
        $currentModelKey = $this->registry->getModelKey($binding->model_type) ?? $binding->model_type;

        return view('workflow::bindings.edit', [
            'binding'         => $binding,
            'processes'       => $processes,
            'models'          => $models,
            'events'          => $events,
            'currentModelKey' => $currentModelKey,
        ]);
    }

    /**
     * Update the specified workflow binding in storage.
     */
    public function update(Request $request, $id)
    {
        $binding = WorkflowBinding::findOrFail($id);

        $request->validate([
            'process_id'   => 'required|exists:processes,id',
            'model_type'   => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!$this->registry->isModelAllowed($value)) {
                        $fail('The selected model type is invalid.');
                    }
                },
            ],
            'event_name'   => [
                'required',
                function ($attribute, $value, $fail) {
                    if (!$this->registry->isEventAllowed($value)) {
                        $fail('The selected event is invalid.');
                    }
                },
            ],
            'priority'     => 'required|integer|min:1',
        ]);

        // Resolve the key to actual FQCN class name
        $modelClass = $this->registry->getModelClass($request->model_type) ?? $request->model_type;

        // Check for duplicates excluding this record
        $duplicate = WorkflowBinding::where('process_id', $request->process_id)
            ->where('model_type', $modelClass)
            ->where('event_name', $request->event_name)
            ->where('id', '!=', $id)
            ->exists();

        if ($duplicate) {
            return back()
                ->withErrors(['process_id' => 'A binding for this process, model, and event combination already exists.'])
                ->withInput();
        }

        $binding->update([
            'process_id'   => $request->process_id,
            'model_type'   => $modelClass,
            'event_name'   => $request->event_name,
            'priority'     => $request->priority,
            'is_active'    => $request->boolean('is_active', false),
        ]);

        return redirect()
            ->route('workflow-bindings.index')
            ->with('success', 'Workflow binding updated successfully.');
    }

    /**
     * Remove the specified workflow binding from storage.
     */
    public function destroy($id)
    {
        $binding = WorkflowBinding::findOrFail($id);
        $binding->delete();

        return redirect()
            ->route('workflow-bindings.index')
            ->with('success', 'Workflow binding deleted successfully.');
    }

    /**
     * Toggle the active status of a workflow binding.
     */
    public function toggleActive($id)
    {
        $binding = WorkflowBinding::findOrFail($id);
        $binding->is_active = !$binding->is_active;
        $binding->save();

        return redirect()
            ->route('workflow-bindings.index')
            ->with('success', 'Workflow binding status toggled successfully.');
    }
}
