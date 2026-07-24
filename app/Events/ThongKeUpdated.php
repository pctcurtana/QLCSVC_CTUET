<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ThongKeUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Danh sách key đã được cập nhật.
     *
     * @var array
     */
    public array $updatedKeys;

    /**
     * Create a new event instance.
     *
     * @param array $updatedKeys
     */
    public function __construct(array $updatedKeys)
    {
        $this->updatedKeys = array_values(array_unique($updatedKeys));
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('dashboard');
    }

    /**
     * Tên event broadcast trên frontend.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'thong-ke.updated';
    }
}
