@extends('layouts.main')
@section('content')
    <div class="max-w-2xl">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Project Details</h1>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="mb-4">
                <h3 class="text-sm font-medium text-gray-500">Name</h3>
                <p class="mt-1 text-gray-900">{{ $project->name }}</p>
            </div>

            <div class="mb-4">
                <h3 class="text-sm font-medium text-gray-500">Description</h3>
                <p class="mt-1 text-gray-900">{{ $project->description ?? 'No description' }}</p>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('projects.edit', $project) }}" class="bg-yellow-500 text-white px-4 py-2 rounded-lg hover:bg-yellow-600">Edit</a>
                <a href="{{ route('projects.index') }}" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-400">Back</a>
            </div>
        </div>

        <div class="mt-8">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800">Tasks</h2>
                <a href="{{ route('tasks.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm">Add Task</a>
            </div>

            @forelse ($project->tasks->sortBy('priority') as $task)
                <div class="bg-white rounded-lg shadow p-4 mb-3 flex items-center gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                #{{ $task->priority }}
                            </span>
                            <a href="{{ route('tasks.show', $task) }}" class="font-medium text-gray-900 hover:text-blue-600">{{ $task->name }}</a>
                        </div>
                        @if ($task->description)
                            <p class="text-sm text-gray-500 mt-1">{{ Str::limit($task->description, 80) }}</p>
                        @endif
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('tasks.edit', $task) }}" class="text-yellow-600 hover:underline text-sm">Edit</a>
                        <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline text-sm" onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg shadow p-4 text-center text-gray-500">
                    No tasks for this project yet.
                </div>
            @endforelse
        </div>
    </div>
@endsection
