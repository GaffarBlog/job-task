<?php

namespace Database\Factories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(3, true),
            'project_id' => 1,
            'description' => fake()->optional(0.7)->sentence(8),
            'priority' => 0,
        ];
    }
}
