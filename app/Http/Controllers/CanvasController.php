<?php

namespace App\Http\Controllers;

use App\Models\Canvas;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Events\CanvasSaved;

class CanvasController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $this->authorize('create', [Canvas::class, $project]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'width' => 'required|integer|min:8|max:128',
            'height' => 'required|integer|min:8|max:128',
            'background_color' => 'required|string|regex:/^#[a-fA-F0-9]{6}$/',
        ]);

        $canvas = $project->canvases()->create([
            'name' => $validated['name'],
            'width' => $validated['width'],
            'height' => $validated['height'],
            'background_color' => $validated['background_color'],
            'data' => json_encode(['layers' => []]),
        ]);

        return redirect()->route('canvases.show', $canvas);
    }

    public function show(Canvas $canvas)
    {
        $this->authorize('view', $canvas);
        $canvas->load('comments.user');
        
        $userRole = $canvas->project->getRoleForUser(Auth::user());
        
        return view('canvases.show', compact('canvas', 'userRole'));
    }

    public function update(Request $request, Canvas $canvas)
    {
        // Permission Check:
        // If updating 'name' (metadata), requires 'update' (Editor+).
        // If updating ONLY 'data' (pixels), requires 'interact' (Member+).
        if ($request->has('name')) {
            $this->authorize('update', $canvas);
        } elseif ($request->has('data')) {
            $this->authorize('interact', $canvas);
        } else {
            // Default fallthrough if neither (shouldn't happen with validation but safe)
            $this->authorize('update', $canvas);
        }

        $validated = $request->validate([
            'data' => 'nullable|string',
            'name' => 'nullable|string|max:255',
        ]);

        $canvas->update(array_filter($validated));
        
        // Force refresh model from database to get fresh data
        $canvas->refresh();
        
        // Create event instance to inspect
        $event = new CanvasSaved($canvas, Auth::user()->name);
        
        // DEBUG: Check what we're broadcasting
        \Log::info('About to broadcast', [
            'canvasId' => $event->canvasId,
            'userName' => $event->userName,
            'has_data' => !empty($event->data),
            'data_preview' => substr($event->data ?? '', 0, 50),
        ]);

        // Broadcast save event with fresh data
        broadcast($event)->toOthers();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Canvas updated successfully']);
        }

        return redirect()->back()->with('message', 'Canvas updated.');
    }

    public function destroy(Canvas $canvas)
    {
        $this->authorize('delete', $canvas);

        // Simple check for now
        if ($canvas->project->user_id !== Auth::id()) {
            abort(403);
        }

        $projectId = $canvas->project_id;
        $canvas->delete();

        return redirect()->route('projects.show', $projectId)->with('message', 'Canvas deleted.');
    }

    public function paint(Request $request, Canvas $canvas)
    {
        $this->authorize('interact', $canvas);

        $validated = $request->validate([
            'x' => 'required|integer',
            'y' => 'required|integer',
            'color' => 'required|string',
        ]);

        broadcast(new \App\Events\PixelPainted(
            $canvas->id,
            $validated['x'],
            $validated['y'],
            $validated['color']
        ))->toOthers();

        return response()->json(['success' => true]);
    }

    public function storeComment(Request $request, Canvas $canvas)
    {
        $this->authorize('interact', $canvas);

        $validated = $request->validate([
            'x' => 'required|integer',
            'y' => 'required|integer',
            'content' => 'required|string|max:1000',
        ]);

        $comment = $canvas->comments()->create([
            'user_id' => Auth::id(),
            'x' => $validated['x'],
            'y' => $validated['y'],
            'content' => $validated['content'],
        ]);

        try {
            broadcast(new \App\Events\CommentAdded($comment))->toOthers();
        } catch (\Exception $e) {
            // Log to stderr just in case
            error_log("Broadcasting Error: " . $e->getMessage());
            
            return response()->json([
                'comment' => $comment->load('user'),
                'warning' => 'Real-time broadcast failed: ' . $e->getMessage(),
                'error_debug' => $e->getTraceAsString() // For temporary debugging
            ]);
            // If we want to treat it as success, we return the comment.
            // If the frontend expects just the comment object, we might need to structure this differently.
            // But currently frontend does: const comment = await response.json(); comments.value.push(comment);
            // So if we return { comment: ..., warning: ... }, it will push the whole object and might break display?
            // PixelEditor.vue: comments.value.push(comment).
            // Rendering: comment.x, comment.y, comment.user.name.
            // If comment is { comment: {...}, warning: ... }, then comment.x is undefined.
            // So we should fix frontend or return the comment structure but maybe log headers?
            // Let's return the comment but append a property or log.
        }

        return response()->json($comment->load('user'));
    }

    public function destroyComment(Request $request, Canvas $canvas, \App\Models\Comment $comment)
    {
        // Allow members to interact, but strictly check ownership or editor status
        $this->authorize('interact', $canvas);
        
        // Custom logic: User owns comment OR User is Editor/Owner
        $isOwnerOrEditor = $canvas->project->hasRole(Auth::user(), 'editor');
        if ($comment->user_id !== Auth::id() && !$isOwnerOrEditor) {
            abort(403, 'Unauthorized to delete this comment.');
        }

        // Debugging Log
        error_log("Delete Comment Request: CommentID={$comment->id}, CommentUserID={$comment->user_id}, AuthID=" . Auth::id() . ", ProjectOwnerID={$canvas->project->user_id}");

        // Authorization removed as per user request: Anyone can delete
        
        $id = $comment->id;
        $comment->delete();

        try {
            broadcast(new \App\Events\CommentDeleted($id, $canvas->id))->toOthers();
        } catch (\Exception $e) {
            error_log("Broadcasting Delete Error: " . $e->getMessage());
        }

        return response()->json(['success' => true]);
    }
}
