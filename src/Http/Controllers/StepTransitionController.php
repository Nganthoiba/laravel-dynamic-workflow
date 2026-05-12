<?php

namespace Workflow\Http\Controllers;

use Workflow\Models\Process;
use Workflow\Models\Step;
use Workflow\Models\StepTransition;
use Illuminate\Http\Request;

class StepTransitionController extends Controller
{
    public function index()
    {
        $transitions = StepTransition::with([
            'process',
            'fromStep',
            'toStep'
        ])->latest()->paginate(10);

        return view(
            'workflow::step_transitions.index',
            compact('transitions')
        );
    }


    public function create()
    {
        $processes = Process::pluck('name', 'id');

        $steps = Step::pluck('name', 'id');

        return view(
            'workflow::step_transitions.create',
            compact('processes', 'steps')
        );
    }


    public function store(Request $request)
    {

        $request->validate([

            'process_id' => 'required|exists:processes,id',

            'from_step_id' => 'required|exists:steps,id',

            'to_step_id' => 'required|exists:steps,id',

            'action_label' => 'required|string|max:255',

            'priority' => 'nullable|integer|min:1'
        ]);


        if ($request->boolean('is_default')) {

            StepTransition::where(
                'from_step_id',
                $request->from_step_id
            )->update([
                'is_default' => false
            ]);
        }


        StepTransition::create([

            'process_id' => $request->process_id,

            'from_step_id' => $request->from_step_id,

            'to_step_id' => $request->to_step_id,

            'condition_json' => $request->condition_json,

            'priority' => $request->priority ?? 1,

            'is_default' => $request->boolean('is_default'),

            'action_label' => $request->action_label,

            'is_active' => $request->boolean('is_active')

        ]);


        return redirect()
            ->route('workflow.step-transitions.index')
            ->with('success', 'Transition created successfully');
    }


    public function show(StepTransition $stepTransition)
    {
        return view(
            'workflow::step_transitions.show',
            compact('stepTransition')
        );
    }


    public function edit(StepTransition $stepTransition)
    {
        $processes = Process::pluck('name', 'id');

        $steps = Step::pluck('name', 'id');

        return view(
            'workflow::step_transitions.edit',
            compact(
                'stepTransition',
                'processes',
                'steps'
            )
        );
    }


    public function update(
        Request $request,
        StepTransition $stepTransition
    )
    {

        $request->validate([

            'process_id' => 'required|exists:processes,id',

            'from_step_id' => 'required|exists:steps,id',

            'to_step_id' => 'required|exists:steps,id',

            'action_label' => 'required|string|max:255',

            'priority' => 'nullable|integer|min:1'
        ]);


        if ($request->boolean('is_default')) {

            StepTransition::where(
                'from_step_id',
                $request->from_step_id
            )->where(
                'id',
                '!=',
                $stepTransition->id
            )->update([
                'is_default' => false
            ]);
        }


        $stepTransition->update([

            'process_id' => $request->process_id,

            'from_step_id' => $request->from_step_id,

            'to_step_id' => $request->to_step_id,

            'condition_json' => $request->condition_json,

            'priority' => $request->priority ?? 1,

            'is_default' => $request->boolean('is_default'),

            'action_label' => $request->action_label,

            'is_active' => $request->boolean('is_active')

        ]);


        return redirect()
            ->route('workflow.step-transitions.index')
            ->with('success', 'Transition updated successfully');
    }


    public function destroy(
        StepTransition $stepTransition
    )
    {

        $stepTransition->delete();

        return redirect()
            ->route('workflow.step-transitions.index')
            ->with('success', 'Transition deleted successfully');
    }
}
