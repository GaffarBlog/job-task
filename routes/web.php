<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });
Route::controller(DashboardController::class)->group(function () {
    Route::get('/', 'index')->name('dashboard.index');
});
Route::controller(TaskController::class)->group(function () {
    Route::get('/tasks', 'index')->name('tasks.index');
    Route::get('/tasks/create', 'create')->name('tasks.create');
    Route::post('/tasks', 'store')->name('tasks.store');
    Route::get('/tasks/{task}', 'show')->name('tasks.show');
    Route::get('/tasks/{task}/edit', 'edit')->name('tasks.edit');
    Route::put('/tasks/{task}', 'update')->name('tasks.update');
    Route::delete('/tasks/{task}', 'destroy')->name('tasks.destroy');
    Route::put('/tasks/priority', 'updatePriority')->name('tasks.updatePriority');
});
Route::controller(ProjectController::class)->group(function () {
    Route::get('/projects', 'index')->name('projects.index');
    Route::get('/projects/create', 'create')->name('projects.create');
    Route::post('/projects', 'store')->name('projects.store');
    Route::get('/projects/{project}', 'show')->name('projects.show');
    Route::get('/projects/{project}/edit', 'edit')->name('projects.edit');
    Route::put('/projects/{project}', 'update')->name('projects.update');
    Route::delete('/projects/{project}', 'destroy')->name('projects.destroy');
});
