<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade'); // Links tasks to a project
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('todo'); // Task status (e.g., 'todo', 'in progress', 'completed')
            $table->date('due_date')->nullable();
            $table->integer('priority')->default(1); // Task priority (e.g., 1 = low, 2 = medium, 3 = high)
            $table->timestamps();
        });        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
