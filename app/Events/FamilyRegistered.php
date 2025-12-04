<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast as BroadcastContract;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FamilyRegistered implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $family;
    public $evacuation_area_id;

    /**
     * Create a new event instance.
     */
    public function __construct($family)
    {
        $this->family = $family;
        $this->evacuation_area_id = $family->evacuation_area_id;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn()
    {
        // Broadcast on a channel for the evacuation area and a global 'families' channel.
        return new Channel('families');
    }

    public function broadcastWith()
    {
        return [
            'family' => $this->family,
        ];
    }
}
