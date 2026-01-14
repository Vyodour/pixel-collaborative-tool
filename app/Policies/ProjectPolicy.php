<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    /**
     * Determine whether the user can view the project.
     */
    public function view(User $user, Project $project): bool
    {
        return $project->is_public || $project->hasRole($user, 'viewer');
    }

    /**
     * Determine whether the user can update the project settings.
     */
    public function update(User $user, Project $project): bool
    {
        return $project->isOwner($user);
    }

    /**
     * Determine whether the user can manage RBAC (invite/kick/roles).
     */
    public function manageRbac(User $user, Project $project): bool
    {
        return $project->isOwner($user);
    }
}
