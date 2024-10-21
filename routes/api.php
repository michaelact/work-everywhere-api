<?php

use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

// Protected Routes - Project Management
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/projects', [ProjectController::class, 'index']); 
    Route::post('/projects', [ProjectController::class, 'store']);
    Route::get('/projects/{id}', [ProjectController::class, 'show']);
    Route::put('/projects/{id}', [ProjectController::class, 'update']);
    Route::delete('/projects/{id}', [ProjectController::class, 'destroy']);
    Route::post('/projects/{id}/assign-users', [ProjectController::class, 'assignUsers']);
    Route::delete('/projects/{id}/remove-users/{userId}', [ProjectController::class, 'removeUser']);

    Route::get('/projects/{projectId}/tasks', [TaskController::class, 'index']);
    Route::post('/projects/{projectId}/tasks', [TaskController::class, 'store']);
    Route::put('/tasks/{id}', [TaskController::class, 'update']);
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);
});

Route::get('/token', function (Request $request) {
    $token = session()->token();

    return response()->json([
        'token' => $token,
    ]);
});

require __DIR__.'/auth.php';
