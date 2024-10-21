<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Project;
use Illuminate\Http\Request;

class TaskController
{
    // Get all tasks for a project (only if the user is assigned to the project)
    public function index($projectId, Request $request)
    {
        $user = $request->user();

        $project = Project::whereHas('users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->findOrFail($projectId);

        $tasks = $project->tasks;

        return response()->json($tasks);
    }

    // Create a new task for a project (only if the user is assigned to the project)
    public function store(Request $request, $projectId)
    {
        $user = $request->user();

        $project = Project::whereHas('users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->findOrFail($projectId);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|string',
            'priority' => 'required|integer',
            'due_date' => 'nullable|date',
        ]);

        $task = $project->tasks()->create($validated);

        return response()->json($task, 201);
    }

    // Update a task
    public function update(Request $request, $id)
    {
        $user = $request->user();

        $task = Task::whereHas('project.users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|string',
            'priority' => 'required|integer',
            'due_date' => 'nullable|date',
        ]);

        $task->update($validated);

        return response()->json($task);
    }

    // Delete a task
    public function destroy($id, Request $request)
    {
        $user = $request->user();

        $task = Task::whereHas('project.users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->findOrFail($id);

        $task->delete();

        return response()->json(['message' => 'Task deleted successfully']);
    }
}
