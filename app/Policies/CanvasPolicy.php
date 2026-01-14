<?php

namespace App\Policies;

use App\Models\Canvas;
use App\Models\User;

class CanvasPolicy
{
    /**
     * Determine whether the user can view the canvas.
     */
    public function view(User $user, Canvas $canvas): bool
    {
        return $canvas->project->is_public || $canvas->project->hasRole($user, 'viewer');
    }

    /**
     * Determine whether the user can create a canvas in the project.
     * (Called on Project instance usually, but for consistency we can use ability check)
     */
    public function create(User $user, \App\Models\Project $project): bool
    {
        return $project->hasRole($user, 'editor');
    }

    /**
     * Determine whether the user can update (rename) the canvas.
     */
    public function update(User $user, Canvas $canvas): bool
    {
        return $canvas->project->hasRole($user, 'editor');
    }

    /**
     * Determine whether the user can delete the canvas.
     */
    public function delete(User $user, Canvas $canvas): bool
    {
        return $canvas->project->hasRole($user, 'editor');
    }

    /**
     * Determine whether the user can interact (draw/comment) with the canvas.
     */
    public function interact(User $user, Canvas $canvas): bool
    {
        return $canvas->project->hasRole($user, 'member');
    }
}
