<?php

namespace App\Events;

use App\Models\Athlete;
use App\Models\Timer;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CyclingStartedEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

	public string $athlete;

    public function __construct($id)
    {
        $timer = Timer::query()
                              ->with('athlete')
                              ->whereId($id)
                              ->first();

		$this->athlete = $timer->athlete->name;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('cycle.started'),
        ];
    }
}
