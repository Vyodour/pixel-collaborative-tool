<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentDeleted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $commentId;
    public $canvasId;

    public function __construct($commentId, $canvasId)
    {
        $this->commentId = $commentId;
        $this->canvasId = $canvasId;
    }

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('canvas.' . $this->canvasId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'comment.deleted';
    }
}
