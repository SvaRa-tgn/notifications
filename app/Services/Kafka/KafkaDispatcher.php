<?php

namespace App\Services\Kafka;

use App\Interface\INotificationDispatcher;
use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Support\Facades\Log;
use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Message\Message;

class KafkaDispatcher implements INotificationDispatcher
{
    /**
     * @param array $data
     * @param string $priority
     * @param string $key
     * @return bool
     */
    public function dispatch(array $data, string $priority, string $key): bool
    {
        $topic = $this->resolveTopic($priority);

        try {
            $message = new Message(
                body: array_merge($data, [
                    'idempotency_key' => $key,
                    'priority'        => $priority,
                    'timestamp'       => CarbonImmutable::now()->toIso8601String(),
                ])
            );

            $message->withKey($key);
            $message->withHeaders([
                'priority'   => $priority,
                'source'     => 'notifications-service',
                'message_id' => $key,
            ]);

            Kafka::publish()
                ->onTopic($topic)
                ->withMessage($message)
                ->send();

            Log::info(
                message: 'Сообщение отправляем в Kafka',
                context: [
                    'topic'    => $topic,
                    'key'      => $key,
                    'priority' => $priority,
                ]
            );

            return true;
        } catch (Exception $e) {
            Log::error(
                message: 'Ошибка отправки сообщения в Kafka',
                context: [
                    'error' => $e->getMessage(),
                    'key' => $key,
                ]
            );

            return false;
        }
    }

    /**
     * @param string $priority
     * @return string
     */
    private function resolveTopic(string $priority): string
    {
        return match ($priority) {
            'transactional' => 'notifications-tx',
            'informational' => 'notifications-info',
            default         => 'notifications-marketing',
        };
    }
}
