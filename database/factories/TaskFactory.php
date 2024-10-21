<?php

namespace Database\Factories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition()
    {
        return [
            'project_id' => \App\Models\Project::factory(),
            'title' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'status' => $this->faker->randomElement(['todo', 'in progress', 'completed']),
            'priority' => $this->faker->numberBetween(1, 3),
            'due_date' => $this->faker->dateTimeBetween('now', '+6 months'),
        ];
    }
}
