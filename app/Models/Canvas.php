<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Canvas extends Model
{
    protected $fillable = [
        'project_id',
        'name',
        'width',
        'height',
        'background_color',
        'thumbnail_path',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Get the project that owns the canvas.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
