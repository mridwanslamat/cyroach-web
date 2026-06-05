<?php
namespace App\Events;
use Illuminate\Broadcasting\Channel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class MissionEnded implements ShouldBroadcastNow
{
    public $data;
    public function __construct($data)
    {
        $this->data = $data;
    }
    public function broadcastOn()
    {
        return new Channel('cyroach-channel');
    }
    public function broadcastAs()
    {
        return 'mission-ended';
    }
}
