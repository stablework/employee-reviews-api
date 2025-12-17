<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        return response()->json(Project::with(['teams', 'reviews'])->get());
    }

    public function show(Project $project)
    {
        return response()->json($project->load(['teams', 'reviews']));
    }

    public function store(Request $request)
    {
        if (! $request->user()->isExecutive()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:projects',
            'description' => 'nullable|string',
        ]);

        $project = Project::create($validated);

        return response()->json($project, 201);
    }

    public function update(Request $request, Project $project)
    {
        if (! $request->user()->isExecutive()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'string|max:255|unique:projects,name,'.$project->id,
            'description' => 'nullable|string',
        ]);

        $project->update($validated);

        return response()->json($project);
    }

    public function destroy(Request $request, Project $project)
    {
        if (! $request->user()->isExecutive()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $project->delete();

        return response()->json(null, 204);
    }

    public function assignTeam(Request $request, Project $project)
    {
        if (! $request->user()->isExecutive()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'team_id' => 'required|exists:teams,id',
        ]);

        $project->teams()->attach($validated['team_id']);

        return response()->json($project->load('teams'));
    }

    public function removeTeam(Request $request, Project $project, Team $team)
    {
        if (! $request->user()->isExecutive()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $project->teams()->detach($team->id);

        return response()->json(null, 204);
    }
}
