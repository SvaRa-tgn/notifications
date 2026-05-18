<?php

namespace App\Repository;

use App\DTO\NotificationCreateDTO;
use App\DTO\NotificationUpdateDTO;
use App\Enums\NotificationStatus;
use App\Interface\INotificationRepository;
use App\Models\Notification;
use Illuminate\Database\Eloquent\Collection;

class NotificationRepository implements INotificationRepository
{
    /**
     * Создаем Notification
     *
     * @param NotificationCreateDTO $dto
     * @return Notification
     */
    public function create(NotificationCreateDTO $dto): Notification
    {
        $notification = new Notification();
        $notification->idempotency_key = $dto->idempotencyKey;
        $notification->recipient_id = $dto->recipientId;
        $notification->channel = $dto->channel;
        $notification->status = NotificationStatus::QUEUED;
        $notification->priority = $dto->priority;
        $notification->message = $dto->message;

        $notification->save();

        return $notification;
    }

    /**
     * Обновляем Notification
     *
     * @param NotificationUpdateDTO $dto
     * @return Notification
     */
    public function update(NotificationUpdateDTO $dto): Notification
    {
        $notification = $dto->notification;
        $notification->status = $dto->status ?? $notification->status;
        $notification->provider_message_id = $dto->providerMessageId ?? $notification->provider_message_id;
        $notification->attempts = $dto->attempts ?? $notification->attempts;
        $notification->delivered_at = $dto->deliveredAt ?? $notification->delivered_at;
        $notification->sent_at = $dto->sentAt ?? $notification->sent_at;
        $notification->bounced_at = $dto->bouncedAt ?? $notification->bounced_at;
        $notification->error_message = $dto->errorMessage ?? $notification->error_message;

        $notification->save();

        return $notification;
    }

    /**
     * Находим все сообщения для Пользователя
     *
     * @param string $recipientId
     * @return mixed
     */
    public function getByUserId(string $recipientId): Collection
    {
        return Notification::where('recipient_id', $recipientId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
