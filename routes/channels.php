<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('canvas.{id}', function ($user, $id) {
    $canvas = \App\Models\Canvas::find($id);
    if (!$canvas) return false;

    // Check if user is owner or member of the project
    $project = $canvas->project;
    if (!$project) return false;

    $isMember = $project->user_id === $user->id || 
                $project->members()->where('user_id', $user->id)->where('project_users.status', 'active')->exists();

    if ($isMember) {
        return ['id' => $user->id, 'name' => $user->name];
    }
    
    return false;
});

// Channel baru untuk Project Chat
Broadcast::channel('project.{id}', function ($user, $id) {
    $project = \App\Models\Project::find($id);
    if (!$project) return false;

    // Logic sama dengan Canvas: Owner ATAU Active Member boleh akses
    $isMember = (int) $project->user_id === (int) $user->id || 
                $project->members()->where('user_id', $user->id)->where('project_users.status', 'active')->exists();

    if ($isMember) {
        return ['id' => $user->id, 'name' => $user->name];
    }

    return false;
});