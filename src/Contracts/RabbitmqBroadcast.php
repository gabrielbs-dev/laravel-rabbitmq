<?php

namespace Gabrielbs\Rabbitmq\Contracts;

abstract class RabbitmqBroadcast
{
    protected $connection = 'rabbitmq';

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|\Illuminate\Broadcasting\Channel[]
     */
    abstract public function broadcastOn();
}
