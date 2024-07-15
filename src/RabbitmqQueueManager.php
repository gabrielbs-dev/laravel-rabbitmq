<?php

namespace Gabrielbs\Rabbitmq;

use Exception;
use Gabrielbs\Rabbitmq\Contracts\RabbitmqBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\Queue;

class RabbitmqQueueManager extends Queue
{
    protected $config;

    public function connect($config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Gets a value from the driver configuration.
     * 
     * @param  string $key
     * @param  mixed $default
     * @return mixed
     */
    protected function getConfig($key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    protected function getBroadcaster()
    {
        return new RabbitmqBroadcaster($this->config);
    }

    public function push($job, $data, $queue)
    {
        $event = $job->event;

        if ($event instanceof RabbitmqBroadcast) {
            $payload = [];

            if (method_exists($event, 'broadcastWith')) {
                $payload = $event->broadcastWith();
            }

            $channels = $event->broadcastOn();

            if (! is_array($channels)) {
                $channels = [$channels];
            }

            $this->getBroadcaster()->broadcast($channels, $event, $payload);
        } else {
            throw new Exception('A classe precisa instaciar ' . RabbitmqBroadcast::class);
        }
    }
}