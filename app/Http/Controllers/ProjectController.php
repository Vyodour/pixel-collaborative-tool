<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_public' => 'boolean',
        ]);

        $project = Project::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'invite_code' => \Illuminate\Support\Str::random(10),
            'is_public' => $validated['is_public'] ?? false,
        ]);

        // Auto-join owner as admin
        $project->users()->attach(Auth::id(), ['role' => 'admin', 'status' => 'active']);

        return redirect()->route('projects.show', $project);
    }

    public function show(Project $project)
    {
        // Load canvases belonging to this project
        $project->load('canvases');
        
        return view('projects.show', compact('project'));
    }

    public function join($code)
    {
        $project = Project::where('invite_code', $code)->firstOrFail();
        
        // If already member, just redirect
        if ($project->users()->where('user_id', Auth::id())->exists()) {
            return redirect()->route('projects.show', $project);
        }

        // Add as pending viewer
        $project->users()->attach(Auth::id(), [
            'role' => 'viewer',
            'status' => 'pending'
        ]);

        return redirect()->route('dashboard')->with('message', 'Join request sent to admin.');
    }
}
