<?php

namespace App\Http\Controllers;

use App\Events\MessageDeleted;
use App\Events\MessageSent;
use App\Events\MessageUpdated;
use App\Models\Message;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index(Project $project)
    {
        if (!$project->hasRole(Auth::user(), 'member') && !$project->isOwner(Auth::user())) {
            abort(403, 'Unauthorized');
        }

        return $project->messages()->with('user')->latest()->take(50)->get()->reverse()->values();
    }

    public function store(Request $request, Project $project)
    {
        if (!$project->hasRole($request->user(), 'member') && !$project->isOwner($request->user())) {
           abort(403, 'Viewers cannot send messages.');
        }

        $validated = $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $message = $project->messages()->create([
            'user_id' => Auth::id(),
            'content' => $validated['content'],
        ]);
        
        $message->load('user');

        broadcast(new MessageSent($message))->toOthers();

        return response()->json($message);
    }

    public function update(Request $request, Project $project, Message $message)
    {
        if ($message->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $message->update(['content' => $validated['content']]);
        
        $message->load('user');
        
        broadcast(new MessageUpdated($message))->toOthers();

        return response()->json($message);
    }

    public function destroy(Project $project, Message $message)
    {
        $isOwner = $project->user_id === Auth::id();
        
        if ($message->user_id !== Auth::id() && !$isOwner) {
            abort(403);
        }

        $message->delete();

        broadcast(new MessageDeleted($message->id, $project->id))->toOthers();

        return response()->json(['status' => 'success']);
    }
}
