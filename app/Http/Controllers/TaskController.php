<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use App\Notifications\TaskAssigned;
use App\Notifications\TaskUpdated;
use Illuminate\Http\Request;

class TaskController
{
    // Get all tasks for a project (only if the user is assigned to the project)
    public function index($projectId, Request $request)
    {
        $user = $request->user();

        $project = Project::whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->findOrFail($projectId);

        $tasks = $project->tasks;

        return response()->json($tasks);
    }

    // Create a new task for a project (only if the user is assigned to the project)
    public function store(Request $request, $projectId)
    {   
        $user = $request->user();

        $project = Project::whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->findOrFail($projectId);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:todo,in_progress,completed',
            'assigned_user_id' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'priority' => 'required|integer|in:1,2,3',
        ]);

        $task = $project->tasks()->create($validated);

        if ($task->assigned_user_id) {
            $user = User::find($task->assigned_user_id);
            $user->notify(new TaskAssigned($task, $projectId));
        }

        return response()->json($task, 201);
    }

    // Update a task
    public function update(Request $request, $id)
    {
        $user = $request->user();

        $task = Task::whereHas('project.members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|string',
            'assigned_user_id' => 'nullable|exists:users,id',
            'priority' => 'required|integer',
            'due_date' => 'nullable|date',
        ]);

        $oldAssignedUserId = $task->assigned_user_id;
        $task->update($validated);

        if ($task->assigned_user_id && $task->assigned_user_id !== $oldAssignedUserId) {
            $user = User::find($task->assigned_user_id);
            $user->notify(new TaskAssigned($task, $task->project_id));
        }

        if ($task->wasChanged('due_date') || $task->wasChanged('status')) {
            if ($task->assigned_user_id) {
                $user = User::find($task->assigned_user_id);
                $user->notify(new TaskUpdated($task, $task->project_id));
            }
        }

        return response()->json($task);
    }

    // Delete a task
    public function destroy($id, Request $request)
    {
        $user = $request->user();

        $task = Task::whereHas('project.members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->findOrFail($id);

        $task->delete();

        return response()->json(['message' => 'Task deleted successfully']);
    }
}
