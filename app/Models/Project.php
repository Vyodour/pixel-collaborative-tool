<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'invite_code',
        'is_public',
    ];

    /**
     * Get the canvases for the project.
     */
    public function canvases()
    {
        return $this->hasMany(Canvas::class);
    }

    /**
     * Get the messages for the project.
     */
    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    protected $casts = [
        'is_public' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The users that belong to the project workspace.
     */
    public function members(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_users')
                    ->withPivot(['role', 'status'])
                    ->withTimestamps();
    }

    /**
     * Pivot records for the project.
     */
    public function projectUsers(): HasMany
    {
        return $this->hasMany(ProjectUser::class);
    }

    /**
     * Check if a user has a specific role in the project.
     */
    public function hasRole(User $user, string $role): bool
    {
        if ($this->user_id === $user->id) {
            return true; // Owner has all permissions
        }

        // Check pivot table
        $member = $this->members()
                       ->where('user_id', $user->id)
                       ->wherePivot('status', 'active')
                       ->first();

        if (!$member) return false;

        $userRole = $member->pivot->role;

        // Role Hierarchy: editor > member > viewer
        if ($role === 'viewer') return true; // All active members are viewers
        
        if ($role === 'member') {
            return in_array($userRole, ['editor', 'member']);
        }
        
        if ($role === 'editor') {
            return $userRole === 'editor';
        }

        return false;
    }

    public function getRoleForUser(User $user): string
    {
        if ($this->isOwner($user)) {
            return 'owner';
        }

        $member = $this->members()
                       ->where('user_id', $user->id)
                       ->wherePivot('status', 'active')
                       ->first();

        // If explicitly a member, return that role. 
        // If not a member but project is public, they are a 'viewer'.
        return $member ? $member->pivot->role : 'viewer';
    }

    public function isOwner(User $user): bool
    {
        return $this->user_id === $user->id;
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'invite_code';
    }
}
