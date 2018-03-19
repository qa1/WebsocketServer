<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class listener extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'listener';

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
        $this->info('[*] Waiting for messages. To exit press CTRL+C');

        $callback = function($msg) {
            $this->warn("[x] Received {$msg->body}");
        };

        \RabbitMQ::channel()->basic_consume(\RabbitMQ::getQueueName(), '', false, true, false, false, $callback);

        while(count(\RabbitMQ::channel()->callbacks)) {
            \RabbitMQ::channel()->wait();
        }
    }
}
