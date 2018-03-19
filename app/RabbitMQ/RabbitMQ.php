<?php

namespace App\RabbitMQ;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQ
{
    public static $queueName = "app";
    public $connection = null;
    public $channel = null;

    public function getQueueName()
    {
        return self::$queueName;
    }

    public function connectIfDisconnected()
    {
        if ($this->connection !== null) {
            return true;
        }

        return $this->connection();
    }

    public function connection()
    {
        if ($this->connection !== null) return $this->connection;
        return $this->connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
    }

    public function channel()
    {
        if ($this->channel !== null) return $this->channel;
        $this->channel = $this->connection()->channel();
        $this->channel->queue_declare($this->getQueueName(), false, false, false, false);
        return $this->channel;
    }

    public function send(AMQPMessage $msg)
    {
        $this->channel()->basic_publish($msg, '', $this->getQueueName());
    }

    public function close()
    {
        $this->channel()->close();
        $this->connection()->close();

        $this->connection = $this->channel = null;
    }
}
