<?php

namespace Gabrielbs\Rabbitmq\Providers;

use Gabrielbs\Rabbitmq\RabbitmqBroadcaster;
use Gabrielbs\Rabbitmq\RabbitmqQueueManager;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Broadcast::extend('rabbitmq', function ($app, array $config) {
            return new RabbitmqBroadcaster($config);
        });

        Queue::extend('rabbitmq', function () {
            return new RabbitmqQueueManager();
        });
    }
}