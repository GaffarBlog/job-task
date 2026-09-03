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
    </div>
@endsection
