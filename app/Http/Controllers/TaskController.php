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

    public function updatePriority(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'taskId' => 'required|exists:tasks,id',
            'order' => 'required|array',
            'order.*.id' => 'required|exists:tasks,id',
            'order.*.priority' => 'required|integer|min:0',
        ]);

        $taskId = $validated['taskId'];
        $allTasks = Task::orderBy('priority')->get()->keyBy('id');
        $newIndex = collect($validated['order'])->pluck('id')->search($taskId);

        $prevPriority = 0;
        $nextPriority = PHP_INT_MAX;

        if ($newIndex > 0) {
            $prevTaskId = $validated['order'][$newIndex - 1]['id'];
            $prevPriority = $allTasks[$prevTaskId]->priority;
        }

        if ($newIndex < count($validated['order']) - 1) {
            $nextTaskId = $validated['order'][$newIndex + 1]['id'];
            $nextPriority = $allTasks[$nextTaskId]->priority;
        }

        $newPriority = $this->findMidpoint($prevPriority, $nextPriority);

        Task::where('id', $taskId)->update(['priority' => $newPriority]);

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
