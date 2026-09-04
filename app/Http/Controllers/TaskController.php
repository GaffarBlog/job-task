<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = Task::with('project')->orderBy('priority')->paginate(10);

        // return $tasks;

        return view('tasks.index', compact('tasks'));
    }

    public function create()
    {
        $projects = Project::orderBy('name')->get();

        return view('tasks.create', compact('projects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'project_id' => 'required|exists:projects,id',
            'description' => 'nullable|string',
        ]);

        $maxPriority = Task::max('priority') ?? 0;

        $validated['priority'] = $maxPriority + 1000;

        Task::create($validated);

        return redirect()->route('tasks.index')->with('success', 'Task created successfully.');
    }

    public function show(Task $task)
    {
        $task->load('project');

        return view('tasks.show', compact('task'));
    }

    public function edit(Task $task)
    {
        $projects = Project::orderBy('name')->get();

        return view('tasks.edit', compact('task', 'projects'));
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'project_id' => 'required|exists:projects,id',
            'description' => 'nullable|string',
        ]);

        $task->update($validated);

        return redirect()->route('tasks.index')->with('success', 'Task updated successfully.');
    }

    public function destroy(Task $task)
    {
        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'Task deleted successfully.');
    }

    // public function updatePriority(Request $request): JsonResponse
    // {
    //     $validated = $request->validate([
    //         'taskId' => 'required|exists:tasks,id',
    //         'previousTaskId' => 'nullable|exists:tasks,id',
    //         'nextTaskId' => 'nullable|exists:tasks,id',
    //     ]);

    //     $taskId = $validated['taskId'];
    //     $previousTaskId = $validated['previousTaskId'];
    //     $nextTaskId = $validated['nextTaskId'];

    //     if ($previousTaskId) {
    //         $prevPriority = Task::where('id', $previousTaskId)->value('priority');
    //     } else {
    //         $prevPriority = 0;
    //     }

    //     if ($nextTaskId) {
    //         $nextPriority = Task::where('id', $nextTaskId)->value('priority');
    //     } else {
    //         $nextPriority = 0;
    //     }
    //     if ($nextPriority === 0 && $prevPriority !== 0) {
    //         $nextPriority = Task::where('priority', '>', $prevPriority)->orderBy('priority')->value('priority') ?? Task::max('priority') + 1000;
    //     } elseif ($nextPriority !== 0 && $prevPriority === 0) {
    //         $prevPriority = Task::where('priority', '<', $nextPriority)->orderBy('priority', 'desc')->value('priority') ?? 1;
    //     } else {
    //         $newPriority = 1000; // Default priority if both are zero
    //     }
    //     $newPriority = $this->findMidpoint($prevPriority, $nextPriority);
    //     Task::where('id', $taskId)->update(['priority' => $newPriority]);

    //     return response()->json(['message' => 'Priority updated successfully.']);
    // }

    public function updatePriority(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'taskId' => 'required|exists:tasks,id',
            'previousTaskId' => 'nullable|exists:tasks,id',
            'nextTaskId' => 'nullable|exists:tasks,id',
        ]);

        $prevPriority = $validated['previousTaskId']
            ? Task::where('id', $validated['previousTaskId'])->value('priority')
            : 0;

        $nextPriority = $validated['nextTaskId']
            ? Task::where('id', $validated['nextTaskId'])->value('priority')
            : 0;

        if ($prevPriority === 0 && $nextPriority !== 0) {
            // Dropped at the very top: find the task just before the next one
            $prevPriority = Task::where('priority', '<', $nextPriority)
                ->orderByDesc('priority')
                ->value('priority') ?? 1;
        } elseif ($nextPriority === 0 && $prevPriority !== 0) {
            // Dropped at the very bottom: find the task just after the previous one
            $nextPriority = Task::where('priority', '>', $prevPriority)
                ->orderBy('priority')
                ->value('priority') ?? Task::max('priority') + 1000;
        }

        $newPriority = $this->findMidpoint($prevPriority, $nextPriority);

        Task::where('id', $validated['taskId'])->update(['priority' => $newPriority]);

        return response()->json(['message' => 'Priority updated successfully.']);
    }

    private function findMidpoint(int $prev, int $next): int
    {
        $candidate = intdiv($prev + $next, 2);

        while (Task::where('priority', $candidate)->exists()) {
            $candidate++;
        }

        return $candidate;
    }
}
