<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PixelPainted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $canvasId;
    public $x;
    public $y;
    public $color;

    public function __construct($canvasId, $x, $y, $color)
    {
        $this->canvasId = $canvasId;
        $this->x = $x;
        $this->y = $y;
        $this->color = $color;
    }

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('canvas.' . $this->canvasId),
        ];
    }

    public function broadcastAs()
    {
        return 'pixel.painted';
    }
}
