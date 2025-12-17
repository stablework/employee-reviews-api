<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Review::with(['reviewer', 'reviewee', 'project']);

        if ($user->isExecutive()) {
            $reviews = $query->get();
        } elseif ($user->isManager()) {
            $teamIds = $user->managedTeams()->pluck('id');
            $projectIds = $user->managedTeams()
                ->with('projects')
                ->get()
                ->pluck('projects.*.id')
                ->flatten();
            $teamMemberIds = $user->managedTeams()
                ->with('members')
                ->get()
                ->pluck('members.*.id')
                ->flatten()
                ->push($user->id);

            $reviews = $query->where(function ($q) use ($projectIds, $teamMemberIds, $user) {
                $q->whereIn('project_id', $projectIds)
                    ->orWhereIn('reviewee_id', $teamMemberIds)
                    ->orWhere('reviewer_id', $user->id);
            })->get();
        } elseif ($user->isAssociate()) {
            $teamIds = $user->teams()->pluck('teams.id');
            $projectIds = $user->teams()
                ->with('projects')
                ->get()
                ->pluck('projects.*.id')
                ->flatten();

            $reviews = $query->where(function ($q) use ($projectIds, $user) {
                $q->whereIn('project_id', $projectIds)
                    ->orWhere('reviewee_id', $user->id)
                    ->orWhere('reviewer_id', $user->id);
            })->get();
        } else {
            $advisorProjectIds = $user->internalAdvisors()->pluck('project_id');
            $reviews = $query->whereIn('project_id', $advisorProjectIds)->get();
        }

        return response()->json($this->formatReviews($reviews, $user));
    }

    public function show(Request $request, Review $review)
    {
        $user = $request->user();

        if (! $this->canViewReview($user, $review)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $review->load(['reviewer', 'reviewee', 'project']);

        return response()->json($this->formatReview($review, $user));
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'reviewee_id' => 'nullable|exists:users,id',
            'content' => 'required|string',
        ]);

        if (! ($validated['project_id'] ?? null) && ! ($validated['reviewee_id'] ?? null)) {
            return response()->json(
                ['message' => 'Either project_id or reviewee_id must be provided'],
                422
            );
        }

        if (! $this->canCreateReview($user, $validated)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $review = Review::create([
            ...$validated,
            'reviewer_id' => $user->id,
        ]);

        $review->load(['reviewer', 'reviewee', 'project']);

        return response()->json($this->formatReview($review, $user), 201);
    }

    public function update(Request $request, Review $review)
    {
        $user = $request->user();

        if ($review->reviewer_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $review->update($validated);
        $review->load(['reviewer', 'reviewee', 'project']);

        return response()->json($this->formatReview($review, $user));
    }

    public function destroy(Request $request, Review $review)
    {
        $user = $request->user();

        if ($user->isExecutive()) {
            $review->delete();

            return response()->json(null, 204);
        }

        if ($review->reviewer_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $review->delete();

        return response()->json(null, 204);
    }

    private function canViewReview($user, Review $review): bool
    {
        if ($user->isExecutive()) {
            return true;
        }

        if ($user->isManager()) {
            $teamIds = $user->managedTeams()->pluck('id');
            $projectIds = $user->managedTeams()
                ->with('projects')
                ->get()
                ->pluck('projects.*.id')
                ->flatten();
            $teamMemberIds = $user->managedTeams()
                ->with('members')
                ->get()
                ->pluck('members.*.id')
                ->flatten()
                ->push($user->id);

            return $review->project_id && in_array($review->project_id, $projectIds->toArray())
                || $review->reviewee_id && in_array($review->reviewee_id, $teamMemberIds->toArray())
                || $review->reviewer_id === $user->id;
        }

        if ($user->isAssociate()) {
            $teamIds = $user->teams()->pluck('teams.id');
            $projectIds = $user->teams()
                ->with('projects')
                ->get()
                ->pluck('projects.*.id')
                ->flatten();

            return $review->project_id && in_array($review->project_id, $projectIds->toArray())
                || $review->reviewee_id === $user->id
                || $review->reviewer_id === $user->id;
        }

        $advisorProjectIds = $user->internalAdvisors()->pluck('project_id');

        return $review->project_id && in_array($review->project_id, $advisorProjectIds->toArray());
    }

    private function canCreateReview($user, array $validated): bool
    {
        if ($user->isExecutive()) {
            return true;
        }

        if ($user->isManager()) {
            $teamIds = $user->managedTeams()->pluck('id');
            $projectIds = $user->managedTeams()
                ->with('projects')
                ->get()
                ->pluck('projects.*.id')
                ->flatten();
            $teamMemberIds = $user->managedTeams()
                ->with('members')
                ->get()
                ->pluck('members.*.id')
                ->flatten()
                ->push($user->id);

            if ($validated['project_id'] ?? null) {
                return in_array($validated['project_id'], $projectIds->toArray());
            }

            return in_array($validated['reviewee_id'] ?? null, $teamMemberIds->toArray());
        }

        if ($user->isAssociate()) {
            $teamIds = $user->teams()->pluck('teams.id');
            $projectIds = $user->teams()
                ->with('projects')
                ->get()
                ->pluck('projects.*.id')
                ->flatten();

            if ($validated['project_id'] ?? null) {
                return in_array($validated['project_id'], $projectIds->toArray());
            }

            return true;
        }

        $advisorProjectIds = $user->internalAdvisors()->pluck('project_id');

        return in_array($validated['project_id'] ?? null, $advisorProjectIds->toArray());
    }

    private function formatReviews($reviews, $user): array
    {
        return $reviews->map(fn ($review) => $this->formatReview($review, $user))->toArray();
    }

    private function formatReview($review, $user): array
    {
        $formatted = $review->toArray();

        if (! $user->isExecutive()) {
            if ($review->reviewer_id !== $user->id) {
                unset($formatted['reviewer']);
            }
        }

        return $formatted;
    }
}
