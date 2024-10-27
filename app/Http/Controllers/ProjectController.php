<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController
{
    // Get all projects assigned to the authenticated user
    public function index(Request $request)
    {
        $user = $request->user();
        $projects = $user->projects()->with('tasks')->get();

        return response()->json($projects);
    }

    // Create a new project
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
        ]);

        $project = Project::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'due_date' => $validated['due_date'],
            'created_by' => $request->user()->id,
        ]);

        $project->users()->attach($request->user()->id);

        return response()->json($project, 201);
    }

    // Show a specific project (if the user is assigned to it)
    public function show($id, Request $request)
    {
        $user = $request->user();

        $project = Project::whereHas('users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with('tasks')->findOrFail($id);

        return response()->json($project);
    }

    // Update an existing project
    public function update(Request $request, $id)
    {
        $user = $request->user();

        $project = Project::whereHas('users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
        ]);

        $project->update($validated);

        return response()->json($project);
    }

    // Delete a project (if the user is assigned to it)
    public function destroy($id, Request $request)
    {
        $user = $request->user();

        $project = Project::whereHas('users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->findOrFail($id);

        $project->delete();

        return response()->json(['message' => 'Project deleted successfully']);
    }

    /**
     * Get summary of projects
     */
    public function getProgress($id)
    {
        $project = Project::findOrFail($id);
        $tasks = $project->tasks;

        // Summary data
        $totalTasks = $tasks->count();
        $completedTasks = $tasks->where('status', 'completed')->count();
        $incompleteTasks = $tasks->where('status', '!=', 'completed')->count();
        $overdueTasks = $tasks->where('status', '!=', 'completed')
                              ->where('deadline', '<', Carbon::now())
                              ->count();

        // Daily completed task breakdown
        $dailyCompletedTasks = $tasks->where('status', 'completed')
            ->groupBy(function ($task) {
                return $task->updated_at->format('Y-m-d');
            })
            ->map(function ($dayTasks) {
                return [
                    'date' => $dayTasks->first()->updated_at->format('Y-m-d'),
                    'completed_count' => $dayTasks->count(),
                ];
            })->values();

        return response()->json([
            'summary' => [
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'incomplete_tasks' => $incompleteTasks,
                'overdue_tasks' => $overdueTasks,
            ],
            'daily_completed_tasks' => $dailyCompletedTasks,
        ]);
    }

    /**
     * Assign additional users to the project
     */
    public function assignUsers(Request $request, $projectId)
    {
        $project = Project::whereHas('users', function ($query) use ($request) {
            $query->where('user_id', $request->user()->id);
        })->findOrFail($projectId);

        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $project->users()->syncWithoutDetaching($validated['user_ids']);

        return response()->json([
            'message' => 'Users successfully assigned to the project',
            'project' => $project->load('users'),
        ]);
    }

    /**
     * Remove a user from the project
     */
    public function removeUser(Request $request, $projectId, $userId)
    {
        $project = Project::whereHas('users', function ($query) use ($request) {
            $query->where('user_id', $request->user()->id);
        })->findOrFail($projectId);

        if ($project->users()->where('id', $userId)->doesntExist()) {
            return response()->json(['message' => 'User not assigned to this project'], 404);
        }

        $project->users()->detach($userId);

        return response()->json([
            'message' => 'User successfully removed from the project',
        ]);
    }
}
