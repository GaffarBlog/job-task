<nav class="bg-white shadow">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center gap-8">
                <h1 class="text-xl font-bold text-gray-800">Dashboard</h1>
                <nav class="flex items-center gap-4">
                    <a href="{{ route('dashboard.index') }}" class="text-sm font-medium text-gray-800 hover:text-blue-600">Dashboard</a>
                    <a href="{{ route('tasks.index') }}" class="text-sm font-medium text-gray-600 hover:text-blue-600">Task</a>
                    <a href="{{ route('projects.index') }}" class="text-sm font-medium text-gray-600 hover:text-blue-600">Project</a>
                </nav>
            </div>
            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-600">Welcome, Admin</span>
            </div>
        </div>
    </div>
</nav>
