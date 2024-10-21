<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = ['project_id', 'title', 'description', 'status', 'due_date', 'priority'];

    // Each task belongs to a project
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
