@extends('layouts.main')
@section('content')
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Projects</h1>
        <a href="{{ route('projects.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Add Project</a>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($projects as $project)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">{{ $project->name }}</td>
                        <td class="px-6 py-4">{{ Str::limit($project->description, 50) }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="{{ route('projects.show', $project) }}" class="text-blue-600 hover:underline mr-3">View</a>
                            <a href="{{ route('projects.edit', $project) }}" class="text-yellow-600 hover:underline mr-3">Edit</a>
                            <form action="{{ route('projects.destroy', $project) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-center text-gray-500">No projects found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $projects->links() }}
    </div>
@endsection
