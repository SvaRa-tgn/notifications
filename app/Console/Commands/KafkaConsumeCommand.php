<?php

namespace App\Console\Commands;

use Carbon\Exceptions\Exception;
use Illuminate\Console\Command;
use Junges\Kafka\Exceptions\ConsumerException;
use Junges\Kafka\Facades\Kafka;
use App\Consumers\NotificationConsumer;

class KafkaConsumeCommand extends Command
{
    protected $signature = 'kafka:consume
                            {--topic= : Топик для чтения}
                            {--timeout=0 : Таймаут в миллисекундах (0 = бесконечно)}';

    protected $description = 'Consume messages from Kafka';

    /**
     * @return int
     * @throws ConsumerException|Exception
     */
    public function handle(): int
    {
        $topic = $this->option('topic') ?? 'notifications';
        $timeout = (int) $this->option('timeout');

        $this->info("Запуск Kafka consumer для topic: {$topic}");

        $consumer = Kafka::consumer([$topic])
            ->withConsumerGroupId(config('kafka.consumer_group_id'))
            ->withAutoCommit()
            ->withHandler(app(NotificationConsumer::class))
            ->build();

        $consumer->consume($timeout);

        return self::SUCCESS;
    }
}
