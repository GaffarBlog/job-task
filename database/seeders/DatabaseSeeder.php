<?php

namespace Database\Seeders;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $projects = Project::factory()->count(10)->create();

        $priority = 1000;
        foreach ($projects as $project) {
            $taskCount = 11;
            for ($i = 1; $i <= $taskCount; $i++) {
                Task::create([
                    'name' => fake()->unique()->words(3, true),
                    'project_id' => $project->id,
                    'description' => fake()->optional(0.7)->sentence(8),
                    'priority' => $priority,
                ]);
                $priority += 1000;
            }
        }
    }
}
