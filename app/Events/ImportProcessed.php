<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ImportProcessed implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $importId;
    public $userId;
    public $module;
    public $status;
    public $total;
    public $created;
    public $updated;
    public $errors;
    public $executionTime;
    public $message;

    /**
     * Create a new event instance.
     *
     * @param int $importId
     * @param int $userId
     * @param string $module
     * @param string $status
     * @param int $total
     * @param int $created
     * @param int $updated
     * @param int $errors
     * @param float|null $executionTime
     * @param string|null $message
     */
    public function __construct(
        $importId,
        $userId,
        $module,
        $status,
        $total = 0,
        $created = 0,
        $updated = 0,
        $errors = 0,
        $executionTime = null,
        $message = null
    ) {
        $this->importId      = (int) $importId;
        $this->userId        = (int) $userId;
        $this->module        = $module;
        $this->status        = $status;
        $this->total         = (int) $total;
        $this->created       = (int) $created;
        $this->updated       = (int) $updated;
        $this->errors        = (int) $errors;
        $this->executionTime = $executionTime !== null ? (float) $executionTime : null;
        $this->message       = $message;
    }

    /**
     * Data to broadcast with the event.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'import_id'      => $this->importId,
            'user_id'        => $this->userId,
            'module'         => $this->module,
            'status'         => $this->status,
            'total'          => $this->total,
            'created'        => $this->created,
            'updated'        => $this->updated,
            'errors'         => $this->errors,
            'execution_time' => $this->executionTime,
            'message'        => $this->message,
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('user.' . $this->userId);
    }

    /**
     * Broadcast event name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'import.processed';
    }
}
