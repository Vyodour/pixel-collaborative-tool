<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $projects = Project::where(function ($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->orWhereHas('members', function ($q) use ($user) {
                      $q->where('user_id', $user->id)
                        ->where('project_users.status', 'active');
                  });
        })
        ->with('canvases')
        ->latest()
        ->get();

        return view('projects.index', compact('projects'));
    }

    public function teams()
    {
        $projects = Project::where('is_public', true)
            ->withCount(['members', 'canvases'])
            ->latest()
            ->get();
            
        return view('pages.teams.index', compact('projects'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_public' => 'boolean',
        ]);

        $project = Project::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'invite_code' => Str::random(10),
            'is_public' => $validated['is_public'] ?? false,
        ]);

        $project->members()->attach(Auth::id(), ['role' => 'editor', 'status' => 'active']);

        return redirect()->route('projects.show', $project);
    }

    public function show(Project $project)
    {
        if (Gate::denies('view', $project)) {
            return view('projects.restricted');
        }
        
        // Check for pending status directly from the relationship
        $membership = $project->members()->where('user_id', Auth::id())->first();

        if ($membership && $membership->pivot->status === 'pending') {
            return view('projects.pending', compact('project'));
        }

        $userRole = $project->getRoleForUser(Auth::user());

        return view('projects.show', compact('project', 'userRole'));
    }

    public function join($code)
    {
        $project = Project::where('invite_code', $code)->firstOrFail();
        
        $project->members()->syncWithoutDetaching([
            Auth::id() => [
                'role' => 'member', 
                'status' => 'pending' 
            ]
        ]);

        return redirect()->route('projects.show', $project)->with('message', 'Joined workspace successfully!');
    }

    public function leave(Project $project)
    {
        if ($project->user_id === Auth::id()) {
            return redirect()->back()->with('error', 'Owner cannot leave.');
        }

        $project->members()->detach(Auth::id());

        return redirect()->route('dashboard')->with('message', 'Left workspace.');
    }

    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $validated['is_public'] = $request->has('is_public');

        $project->update($validated);

        return redirect()->back()->with('message', 'Workspace updated.');
    }

    public function destroy(Project $project)
    {
        $this->authorize('update', $project);

        $project->delete();

        return redirect()->route('dashboard')->with('message', 'Workspace erased.');
    }
}
