<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory as LoopFactory;
use React\Socket\Server as Reactor;

class MyChat implements MessageComponentInterface {
    protected $clients;

    public function __construct($loop) {
        $this->clients = new \SplObjectStorage;

        $loop->addPeriodicTimer(1, function(){

            \RabbitMQ::connectIfDisconnected();
            $count = 0;

            do {
                if ($message = \RabbitMQ::channel()->basic_get(\RabbitMQ::getQueueName(), true)) {
                    foreach($this->clients as $client) {
                        $client->send($message->body);
                    }
                }

                $count++;
            } while ( $count < 100 );
        });
    }

    public function onOpen(ConnectionInterface $conn) {
        $this->clients->attach($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        foreach ($this->clients as $client) {
            $client->send($msg);
        }
    }

    public function onClose(ConnectionInterface $conn) {
        $this->clients->detach($conn);
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        $conn->close();
    }
}

class socket extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'socket';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Start listening on port 8080');

        $loop = LoopFactory::create();
        $socket = new Reactor('0.0.0.0:8080', $loop);

        $server = new IoServer(
            new HttpServer(
                new WsServer(
                    new MyChat($loop)
                )
            ),
            $socket,
            $loop
        );

        $server->run();
    }
}
