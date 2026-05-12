<?php

namespace Workflow\Http\Controllers;

use Workflow\Models\Process;
use Illuminate\Http\Request;

class ProcessController extends Controller
{
    /**
     * Display list of workflow processes
     */
    public function index()
    {
        $processes = Process::withCount('steps')->latest()->paginate(10);

        return view(
            'workflow::processes.index',
            compact('processes')
        );
    }


    /**
     * Show create process form
     */
    public function create()
    {
        return view('workflow::processes.create');
    }


    /**
     * Store new workflow process
     */
    public function store(Request $request)
    {

        $request->validate([

            'name' => 'required|string|max:255',

            'code' => 'required|string|max:255|unique:processes'

        ]);


        Process::create([

            'name' => $request->name,

            'code' => $request->code,

            'description' => $request->description,

            'is_active' => $request->boolean('is_active')

        ]);


        return redirect()
            ->route('workflow.processes.index')
            ->with('success', 'Process created successfully');
    }


    /**
     * Display process details
     */
    public function show(Process $process)
    {
        $process->load([
            'steps.roles',
            'steps.outgoingEdges.toStep',
            'workflowInstances'
        ]);

        return view(
            'workflow::processes.show',
            compact('process')
        );
    }


    /**
     * Show edit form
     */
    public function edit(Process $process)
    {
        return view(
            'workflow::processes.edit',
            compact('process')
        );
    }


    /**
     * Update workflow process
     */
    public function update(
        Request $request,
        Process $process
    )
    {

        $request->validate([

            'name' => 'required|string|max:255',

            'code' => "required|string|max:255|unique:processes,code,$process->id"

        ]);


        $process->update([

            'name' => $request->name,

            'code' => $request->code,

            'description' => $request->description,

            'is_active' => $request->boolean('is_active')

        ]);


        return redirect()
            ->route('workflow.processes.index')
            ->with('success', 'Process updated successfully');
    }


    /**
     * Delete workflow process
     */
    public function destroy(Process $process)
    {

        $process->delete();

        return redirect()
            ->route('workflow.processes.index')
            ->with('success', 'Process deleted successfully');
    }
}
