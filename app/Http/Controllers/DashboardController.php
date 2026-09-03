<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;

class DashboardController extends Controller
{
    public function index()
    {
        $totalTasks = Task::count();
        $totalProjects = Project::count();

        return view('dashboard.index', compact('totalTasks', 'totalProjects'));
    }
}
