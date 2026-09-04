@extends('layouts.main')
@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Tasks</h1>
        <a href="{{ route('tasks.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Add Task</a>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div id="task-list" data-sortable>
        @forelse ($tasks as $task)
            <div class="bg-white rounded-lg shadow p-4 mb-3 flex items-center gap-4 cursor-move draggable-item" data-task-id="{{ $task->id }}">
                <div class="drag-handle text-gray-400 hover:text-gray-600">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M7 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 2a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 8a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM7 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4zM13 14a2 2 0 1 0 0 4 2 2 0 0 0 0-4z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <a href="{{ route('tasks.show', $task) }}" class="font-medium text-gray-900 hover:text-blue-600">{{ $task->name }}</a>
                    <div class="flex items-center gap-2 mt-1">
                        <a href="{{ route('projects.show', $task->project) }}" class="text-sm text-blue-600 hover:underline">{{ $task->project->name }}</a>
                        @if ($task->description)
                            <span class="text-gray-400">·</span>
                            <span class="text-sm text-gray-500">{{ Str::limit($task->description, 60) }}</span>
                        @endif
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('tasks.edit', $task) }}" class="text-yellow-600 hover:underline">Edit</a>
                    <form action="{{ route('tasks.destroy', $task) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('Are you sure?')">Delete</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow p-4 text-center text-gray-500">
                No tasks found.
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $tasks->links() }}
    </div>
@endsection
