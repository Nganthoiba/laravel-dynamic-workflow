<?php

namespace Workflow\Http\Controllers;

use Workflow\Models\Process;
use Workflow\Models\Step;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;

class StepController extends Controller
{
    /**
     * List steps
     */
    public function index()
    {
        $steps = Step::with('process')
            ->latest()
            ->paginate(10);

        return view('workflow::steps.index', compact('steps'));
    }


    /**
     * Show create form
     */
    public function create()
    {
        $processes = Process::pluck('name', 'id');
        
        $roleModelClass = Config::get('workflow.models.role');
        $roles = $roleModelClass::pluck('display_name', 'id');

        return view(
            'workflow::steps.create',
            compact('processes', 'roles')
        );
    }


    /**
     * Store step
     */
    public function store(Request $request)
    {

        $request->validate([

            'process_id' => 'required|exists:processes,id',

            'name' => 'required|string|max:255',

            'code' => 'required|string|max:255|unique:steps',

            'workflow_action' => 'required|string',

            'stage_level' => 'nullable|integer|min:1'

        ]);


        // Ensure only ONE start step per process

        if ($request->boolean('is_start')) {

            Step::where('process_id', $request->process_id)
                ->where('is_start', true)
                ->update(['is_start' => false]);
        }


        $step = Step::create([

            'process_id' => $request->process_id,

            'name' => $request->name,

            'code' => $request->code,

            'workflow_action' => $request->workflow_action,

            'stage_level' => $request->stage_level,

            'is_start' => $request->boolean('is_start'),

            'is_end' => $request->boolean('is_end'),

            'is_active' => $request->boolean('is_active')

        ]);


        // Assign roles

        if ($request->roles) {

            $step->roles()->sync($request->roles);
        }


        return redirect()
            ->route('workflow.steps.index')
            ->with('success', 'Step created successfully');
    }


    /**
     * Show step details
     */
    public function show(Step $step)
    {
        return view('workflow::steps.show', compact('step'));
    }


    /**
     * Edit step
     */
    public function edit(Step $step)
    {
        $processes = Process::pluck('name', 'id');

        $roleModelClass = Config::get('workflow.models.role');
        $roles = $roleModelClass::pluck('display_name', 'id');

        return view(
            'workflow::steps.edit',
            compact('step', 'processes', 'roles')
        );
    }


    /**
     * Update step
     */
    public function update(Request $request, Step $step)
    {

        $request->validate([

            'process_id' => 'required|exists:processes,id',

            'name' => 'required|string|max:255',

            'code' => "required|string|max:255|unique:steps,code,$step->id",

            'workflow_action' => 'required|string',

            'stage_level' => 'nullable|integer|min:1'

        ]);


        if ($request->boolean('is_start')) {

            Step::where('process_id', $request->process_id)
                ->where('is_start', true)
                ->where('id', '!=', $step->id)
                ->update(['is_start' => false]);
        }


        $step->update([

            'process_id' => $request->process_id,

            'name' => $request->name,

            'code' => $request->code,

            'workflow_action' => $request->workflow_action,

            'stage_level' => $request->stage_level,

            'is_start' => $request->boolean('is_start'),

            'is_end' => $request->boolean('is_end'),

            'is_active' => $request->boolean('is_active')

        ]);


        $step->roles()->sync($request->roles ?? []);


        return redirect()
            ->route('workflow.steps.index')
            ->with('success', 'Step updated successfully');
    }


    /**
     * Delete step
     */
    public function destroy(Step $step)
    {
        $step->delete();

        return redirect()
            ->route('workflow.steps.index')
            ->with('success', 'Step deleted successfully');
    }
}
