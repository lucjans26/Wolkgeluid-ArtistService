<?php

namespace App\Console\Commands;

use App\Traits\AuthTrait;
use Illuminate\Console\Command;
use Kunnu\RabbitMQ\RabbitMQExchange;
use Kunnu\RabbitMQ\RabbitMQGenericMessageConsumer;
use Kunnu\RabbitMQ\RabbitMQIncomingMessage;
use Kunnu\RabbitMQ\RabbitMQQueue;

class RabbitUserConsumer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rabbitmq:user-consumer {--exchange=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'My consumer command';

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
        $rabbitMQ = app('rabbitmq');
        $messageConsumer = new RabbitMQGenericMessageConsumer(
            function (RabbitMQIncomingMessage $message) {
                $pload = json_decode($message->getStream(), true);
                switch ($pload['action']) {
                    case 'login':
                        AuthTrait::registerToken($pload['token']);
                        break;
                    case 'logout':
                        AuthTrait::deleteTokens($pload['user_id']);
                        break;
                    case 'delete':
                        AuthTrait::deleteUser($pload['user_id']);
                        break;
                    default:
                        break;
                }
                $message->getDelivery()->acknowledge();
            },
            $this, // Scope the closure to the command
        );

        $routingKey = 'user';
        $queue = new RabbitMQQueue('artist_queue', ['declare' => true]);
        $exchange = new RabbitMQExchange($this->option('exchange') ?? '', ['declare' => true]);

        $messageConsumer
            ->setExchange($exchange)
            ->setQueue($queue);

        $rabbitMQ->consumer()->consume($messageConsumer, $routingKey);
    }
}
