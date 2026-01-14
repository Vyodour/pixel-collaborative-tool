<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = ['canvas_id', 'user_id', 'content', 'x', 'y'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function canvas()
    {
        return $this->belongsTo(Canvas::class);
    }
}
