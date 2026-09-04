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
            'previousTaskId' => 'nullable|exists:tasks,id',
            'nextTaskId' => 'nullable|exists:tasks,id',
        ]);

        $taskId = $validated['taskId'];
        $previousTaskId = $validated['previousTaskId'];
        $nextTaskId = $validated['nextTaskId'];

        $previousPriority = $previousTaskId
            ? Task::whereKey($previousTaskId)->value('priority')
            : null;

        $nextPriority = $nextTaskId
            ? Task::whereKey($nextTaskId)->value('priority')
            : null;

        $newPriority = $this->calculatePriority(
            $previousPriority,
            $nextPriority
        );

        Task::whereKey($taskId)->update([
            'priority' => $newPriority,
        ]);

        return response()->json([
            'message' => 'Priority updated successfully.',
        ]);
    }

    private function calculatePriority(
        ?int $previousPriority,
        ?int $nextPriority
    ): int {
        // Moving between two tasks
        if ($previousPriority !== null && $nextPriority !== null) {
            return $this->findMidpoint(
                $previousPriority,
                $nextPriority
            );
        }

        // Moving to the end of the list
        if ($previousPriority !== null) {
            return $this->getNextAvailablePriority(
                $previousPriority + 1
            );
        }

        // Moving to the beginning of the list
        if ($nextPriority !== null) {
            return $this->getPreviousAvailablePriority(
                $nextPriority - 1
            );
        }

        // No neighbouring tasks
        return 1000;
    }

    private function getNextAvailablePriority(int $priority): int
    {
        while (Task::where('priority', $priority)->exists()) {
            $priority++;
        }

        return $priority;
    }

    private function getPreviousAvailablePriority(int $priority): int
    {
        while (Task::where('priority', $priority)->exists()) {
            $priority--;
        }

        return $priority;
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
