<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'start_at', 'end_at'];

    protected $dates = ['start_at', 'end_at'];

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
