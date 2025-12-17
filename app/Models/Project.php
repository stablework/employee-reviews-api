<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = ['name', 'description'];

    public function teams()
    {
        return $this->belongsToMany(Team::class, 'project_team');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function internalAdvisors()
    {
        return $this->hasMany(InternalAdvisor::class);
    }
}
