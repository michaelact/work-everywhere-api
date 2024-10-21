<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController
{
    // Get all projects assigned to the authenticated user
    public function index(Request $request)
    {
        $user = $request->user();
        $projects = $user->projects()->with('tasks')->get(); // Include tasks for each project

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

        // Attach the creator as a user assigned to the project
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

        // Ensure the user is assigned to the project
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

        // Ensure the user is assigned to the project
        $project = Project::whereHas('users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->findOrFail($id);

        $project->delete();

        return response()->json(['message' => 'Project deleted successfully']);
    }

    /**
     * Assign additional users to the project
     */
    public function assignUsers(Request $request, $projectId)
    {
        $project = Project::whereHas('users', function ($query) use ($request) {
            $query->where('user_id', $request->user()->id);
        })->findOrFail($projectId);

        // Validate that the input is an array of user IDs
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id', // Ensure each user ID exists
        ]);

        // Attach additional users to the project (avoiding duplicates)
        $project->users()->syncWithoutDetaching($validated['user_ids']);

        return response()->json([
            'message' => 'Users successfully assigned to the project',
            'project' => $project->load('users'), // Load assigned users to return in response
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

        // Ensure the user exists and is currently assigned to the project
        if ($project->users()->where('id', $userId)->doesntExist()) {
            return response()->json(['message' => 'User not assigned to this project'], 404);
        }

        // Detach the user from the project
        $project->users()->detach($userId);

        return response()->json([
            'message' => 'User successfully removed from the project',
        ]);
    }
}
