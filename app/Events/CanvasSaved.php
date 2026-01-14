<?php

namespace App\Events;

use App\Models\Canvas;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CanvasSaved implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $canvasId;
    public $userName;
    public $data;

    /**
     * Create a new event instance.
     */
    public function __construct(Canvas $canvas, $userName)
    {
        $this->canvasId = $canvas->id;
        $this->userName = $userName;
        $this->data = $canvas->data;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('canvas.' . $this->canvasId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'canvas.saved';
    }
}
