<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $fillable = ['name', 'manager_id'];

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'team_user');
    }

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'project_team');
    }

    public function internalAdvisors()
    {
        return $this->hasMany(InternalAdvisor::class);
    }
}
