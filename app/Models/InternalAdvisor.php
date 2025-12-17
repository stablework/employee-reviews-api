<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternalAdvisor extends Model
{
    protected $fillable = ['user_id', 'project_id', 'team_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}
