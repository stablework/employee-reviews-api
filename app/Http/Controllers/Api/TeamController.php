<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index()
    {
        return response()->json(Team::with(['manager', 'members', 'projects'])->get());
    }

    public function show(Team $team)
    {
        return response()->json($team->load(['manager', 'members', 'projects']));
    }

    public function store(Request $request)
    {
        if (! $request->user()->isExecutive()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'manager_id' => 'required|exists:users,id',
        ]);

        $team = Team::create($validated);

        return response()->json($team, 201);
    }

    public function update(Request $request, Team $team)
    {
        if (! $request->user()->isExecutive()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'name' => 'string|max:255',
            'manager_id' => 'exists:users,id',
        ]);

        $team->update($validated);

        return response()->json($team);
    }

    public function destroy(Request $request, Team $team)
    {
        if (! $request->user()->isExecutive()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $team->delete();

        return response()->json(null, 204);
    }

    public function addMember(Request $request, Team $team)
    {
        if (! $request->user()->isExecutive()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $team->members()->attach($validated['user_id']);

        return response()->json($team->load('members'));
    }

    public function removeMember(Request $request, Team $team, User $user)
    {
        if (! $request->user()->isExecutive()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $team->members()->detach($user->id);

        return response()->json(null, 204);
    }
}
