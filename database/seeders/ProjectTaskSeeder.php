<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;

class ProjectTaskSeeder extends Seeder
{
    public function run()
    {
        Project::factory(10)
            ->hasTasks(10)
            ->create()
            ->each(function ($project) {
                $project->members()->attach(1);
            });
    }
}
