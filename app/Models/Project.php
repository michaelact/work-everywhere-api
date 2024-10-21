<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'due_date', 'created_by'];

    // A project can have many tasks
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    // A project can have many users assigned
    public function users()
    {
        return $this->belongsToMany(User::class, 'project_user');
    }
}
