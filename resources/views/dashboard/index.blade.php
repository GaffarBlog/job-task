@extends('layouts.main')
@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-sm font-medium text-gray-500">Total Task</h3>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $totalTasks ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-sm font-medium text-gray-500">Total Project</h3>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $totalProjects ?? 0 }}</p>
        </div>
    </div>
@endsection
