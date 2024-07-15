<?php

namespace Gabrielbs\Rabbitmq;

use Gabrielbs\Rabbitmq\Exceptions\ConnectionClosedException;
use Gabrielbs\Rabbitmq\Exceptions\InvalidChannelException;
use Illuminate\Broadcasting\Broadcasters\Broadcaster;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Illuminate\Contracts\Broadcasting\Broadcaster as BroadcasterContract;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Exception\AMQPConnectionClosedException;

class RabbitmqBroadcaster extends Broadcaster implements BroadcasterContract
{
    protected $config;

    protected $connection;

    protected $channel;

    /**
     * @param  array $config
     * @return void
     */
    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * Authenticate the incoming request for a given channel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function auth($request)
    {
        //
    }

    /**
     * Return the valid authentication response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $result
     * @return mixed
     */
    public function validAuthenticationResponse($request, $result)
    {
        //
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

    /**
     * Gets an existing instance of the connection or creates it if none exists.
     * 
     * @return AMQPStreamConnection
     */
    protected function getConnection()
    {
        if (is_null($this->connection)) {
            $this->connection = new AMQPStreamConnection(
                $this->getConfig('host', 'localhost'),
                $this->getConfig('port', 5672),
                $this->getConfig('user', 'guest'),
                $this->getConfig('password', 'guest')
            );
        }

        return $this->connection;
    }

    /**
     * Gets an existing instance of the channel or creates it if none exists.
     * 
     * @return AMQPChannel
     */
    protected function getChannel()
    {
        if (is_null($this->channel)) {
            $connection = $this->getConnection();

            $this->channel = $connection->channel();
        }

        return $this->channel;
    }

    /**
     * Publish a message on a specific exchange.
     * 
     * @param  string $name
     * @param  string $route
     * @param  AMQPMessage $message
     * @return void
     */
    protected function publishInExchange($name, $route, $message)
    {
        $this->getChannel()->basic_publish($message, '', $name, $route);
    }

    /**
     * Publish a message on a specific queue.
     * 
     * @param  string $name
     * @param  AMQPMessage $message
     * @return void
     */
    protected function publishInQueue($name, $message)
    {
        $this->getChannel()->basic_publish($message, '', $name);
    }

    /**
     * Prepare a message for sending.
     * 
     * @param  array $payload
     * @return AMQPMessage
     */
    protected function getMessage($payload)
    {
        return new AMQPMessage(json_encode($payload));
    }

    /**
     * Closes the channel and connection if they are open
     * 
     * @return void
     */
    protected function close()
    {
        if (! is_null($this->channel) && $this->channel->is_open()) {
            $this->channel->close();
        }

        if (! is_null($this->connection) && $this->connection->isConnected()) {
            $this->connection->close();
        }
    }

    /**
     * Broadcast the given event.
     *
     * @param  array  $channels
     * @param  string  $event
     * @param  array  $payload
     * @return void
     */
    public function broadcast($channels, $event, $payload = [])
    {
        try {
            $message = $this->getMessage($payload);

            foreach ($channels as $channel) {
                if ($channel instanceof QueueChannel) {
                    $this->publishInQueue(
                        $channel->name,
                        $message
                    );
                } else if ($channel instanceof ExchangeChannel) {
                    $this->publishInExchange(
                        $channel->key,
                        $channel->name,
                        $message
                    );
                } else {
                    throw new InvalidChannelException();
                }
            }

            $this->close();
        } catch (AMQPConnectionClosedException $ex) {
            throw new ConnectionClosedException($ex->getMessage());
        }
    }
}