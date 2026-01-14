<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectMemberController extends Controller
{
    /**
     * Update member role or status (Approve/Reject).
     */
    public function update(Request $request, Project $project, User $user)
    {
        // Only owner can manage members
        if (!$project->isOwner(Auth::user())) {
            abort(403);
        }

        $validated = $request->validate([
            'role' => 'nullable|in:editor,member,viewer',
            'status' => 'required|in:active,pending,declined'
        ]);

        $project->members()->updateExistingPivot($user->id, array_filter($validated));

        if ($validated['status'] === 'declined') {
            $project->members()->detach($user->id);
            return redirect()->back()->with('message', 'Member request declined.');
        }

        return redirect()->back()->with('message', 'Member updated successfully.');
    }

    /**
     * Remove member from project.
     */
    public function destroy(Project $project, User $user)
    {
        // Only owner can manage members
        if (!$project->isOwner(Auth::user())) {
            abort(403);
        }

        if ($project->user_id === $user->id) {
            return redirect()->back()->with('error', 'Cannot remove the owner.');
        }

        $project->members()->detach($user->id);

        return redirect()->back()->with('message', 'Member removed.');
    }
}
