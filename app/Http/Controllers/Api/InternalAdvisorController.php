<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InternalAdvisor;
use Illuminate\Http\Request;

class InternalAdvisorController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->isExecutive()) {
            $advisors = InternalAdvisor::with(['user', 'project', 'team'])->get();
        } else {
            $advisors = $user->internalAdvisors()->with(['project', 'team'])->get();
        }

        return response()->json($advisors);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        if (! ($user->isManager() || $user->isAssociate())) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'team_id' => 'required|exists:teams,id',
        ]);

        $advisor = InternalAdvisor::create([
            ...$validated,
            'user_id' => $user->id,
        ]);

        return response()->json($advisor->load(['user', 'project', 'team']), 201);
    }

    public function destroy(Request $request, InternalAdvisor $advisor)
    {
        $user = $request->user();

        if ($user->id !== $advisor->user_id && ! $user->isExecutive()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $advisor->delete();

        return response()->json(null, 204);
    }
}
