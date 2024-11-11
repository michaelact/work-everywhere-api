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
        $projects = $user->projects()->with('members')->get();

        return response()->json($projects);
    }

    // Create a new project
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'member_ids' => 'array',
            'member_ids.*' => 'exists:users,id',
        ]);

        $project = Project::create($validated);

        if (isset($validated['member_ids'])) {
            $project->members()->attach($validated['member_ids']);
        }

        return response()->json($project, 201);
    }

    // Show a specific project (if the user is assigned to it)
    public function show($id, Request $request)
    {
        $user = $request->user();

        $project = Project::whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->with(['tasks', 'members'])->findOrFail($id);

        return response()->json($project);
    }

    // Update an existing project
    public function update(Request $request, $id)
    {
        $user = $request->user();

        $project = Project::whereHas('members', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'required|date',
            'member_ids' => 'array',
            'member_ids.*' => 'exists:users,id',
        ]);

        if (isset($validated['member_ids'])) {
            $project->members()->sync($validated['member_ids']);
        }

        return response()->json($project);
    }

    // Delete a project (if the user is assigned to it)
    public function destroy($id, Request $request)
    {
        $user = $request->user();

        $project = Project::whereHas('members', function ($query) use ($user) {
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
                              ->where('due_date', '<', Carbon::now())
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
    public function addMember(Request $request, $projectId)
    {
        $project = Project::whereHas('members', function ($query) use ($request) {
            $query->where('user_id', $request->user()->id);
        })->findOrFail($projectId);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $project->members()->attach($validated['user_id']);
        return response()->json($project->load('members'));
    }

    /**
     * Remove a user from the project
     */
    public function removeMember(Request $request, $projectId)
    {
        $project = Project::whereHas('members', function ($query) use ($request) {
            $query->where('user_id', $request->user()->id);
        })->findOrFail($projectId);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        if ($project->members()->where('user_id', $validated['user_id'])->doesntExist()) {
            return response()->json(['message' => 'User not assigned to this project'], 404);
        }

        $memberCount = $project->members()->count();
        if ($memberCount <= 1) {
            return response()->json(['message' => 'You cannot remove the last member of the project'], 400);
        }

        $project->members()->detach($validated['user_id']);
        return response()->json($project->load('members'));
    }
}
