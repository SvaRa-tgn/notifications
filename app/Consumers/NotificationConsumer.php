<?php

namespace App\Consumers;

use App\DTO\NotificationCreateDTO;
use App\DTO\NotificationUpdateDTO;
use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use App\Interface\INotificationRepository;
use App\Models\Notification;
use App\Services\Email\EmailProviderService;
use App\Services\Sms\SmsProviderService;
use Carbon\CarbonImmutable;
use Exception;
use Illuminate\Support\Facades\DB;
use Junges\Kafka\Contracts\ConsumerMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Junges\Kafka\Facades\Kafka;
use Junges\Kafka\Message\Message;
use Throwable;

class NotificationConsumer
{
    public function __construct(
        private readonly SmsProviderService $smsProvider,
        private readonly EmailProviderService $emailProvider,
        private readonly INotificationRepository $notificationRepository,
    ) {}

    /**
     * Обработка и отправка сообщения
     *
     * @param ConsumerMessage $message
     * @return void
     */
    public function __invoke(ConsumerMessage $message): void
    {
        $body = $message->getBody();
        $key = $message->getKey();
        $headers = $message->getHeaders();


        Log::info(
            message: 'Отправляем сообщение в Kafka',
            context: [
                'topic'     => $message->getTopicName(),
                'key'       => $key,
                'partition' => $message->getPartition(),
                'offset'    => $message->getOffset(),
                'priority'  => $headers['priority'] ?? 'unknown',
            ]
        );

        $idempotencyKey = $body['idempotency_key'] ?? $key;

        if ($this->isDuplicate($idempotencyKey)) {
            Log::warning(
                message: 'Дублирующее сообщение, пропускаем',
                context: ['key' => $idempotencyKey],
            );
            return;
        }

        try {
            DB::transaction(function () use ($body, $idempotencyKey) {
                $notification = $this->notificationRepository->create(
                    new NotificationCreateDTO(
                        status: NotificationStatus::QUEUED,
                        idempotencyKey: $idempotencyKey,
                        recipientId: $body['recipient_id'],
                        channel: $body['channel'],
                        priority: $body['priority'],
                        message: $body['message'],
                    ),
                );

                $this->sendToProvider($notification);
            });

            $this->markAsProcessed($idempotencyKey);

            Log::info(
                message: 'Сообщение успешно обработано',
                context: [
                    'key' => $idempotencyKey,
                ],
            );
        } catch (Throwable $e) {
            Log::error(
                message: 'Не удалось обработать сообщение',
                context: [
                    'key'   => $idempotencyKey,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ],
            );

            $this->sendToDLQ($message, $e);
        }
    }

    /**
     * Отправить уведомление через нужный провайдер.
     *
     * @param Notification $notification
     * @return void
     * @throws Throwable
     */
    private function sendToProvider(Notification $notification): void
    {
        try {
            $method = $notification->channel === NotificationChannel::SMS
                ? $this->smsProvider
                : $this->emailProvider;

            $result = $method->send($notification->recipient_id, $notification->message);

            if ($result['success']) {
                $this->notificationRepository->update(
                    new NotificationUpdateDTO(
                        notification: $notification,
                        status: NotificationStatus::SENT,
                        providerMessageId: $result['provider_message_id'],
                        sentAt: CarbonImmutable::now(),
                    )
                );
                // Имитация: через 1 секунду помечаем как доставленное
                // В реальности — через вебхук от провайдера
                // $notification->markAsDelivered();
            }
        } catch (Throwable $e) {
            $this->notificationRepository->update(
                new NotificationUpdateDTO(
                    notification: $notification,
                    status: NotificationStatus::BOUNCED,
                    errorMessage: $e->getMessage(),
                    bouncedAt: CarbonImmutable::now(),
                ),
            );
            throw $e; // Пробрасываем для ретрая
        }
    }

    /**
     * @param string $key
     * @return bool
     */
    private function isDuplicate(string $key): bool
    {
        return Cache::has("processed:{$key}");
    }

    /**
     * @param string $key
     * @return void
     */
    private function markAsProcessed(string $key): void
    {
        Cache::put("processed:{$key}", true, now()->addDays(7));
    }

    /**
     * @param ConsumerMessage $message
     * @param Throwable $e
     * @return void
     */
    private function sendToDLQ(ConsumerMessage $message, \Throwable $e): void
    {
        try {
            Kafka::publish()
                ->onTopic('notifications-dlq')
                ->withMessage(
                    Message::create()
                        ->withBody([
                            'original_message' => $message->getBody(),
                            'error'            => $e->getMessage(),
                            'failed_at'        => now()->toIso8601String(),
                        ])
                        ->withKey($message->getKey())
                        ->withHeaders([
                            'original_topic' => $message->getTopicName(),
                            'error_type'     => get_class($e),
                        ])
                )
                ->send();

            Log::info(
                message: 'Сообщение отправлено в DLQ',
                context: ['key' => $message->getKey()],
            );
        } catch (Exception $dlqError) {
            Log::critical(
                message: 'Не удалась отправка в DLQ',
                context: [
                    'error'         => $dlqError->getMessage(),
                    'original_key' => $message->getKey(),
                ],
            );
        }
    }
}
