<?php

namespace App\Interface;

use App\DTO\NotificationCreateDTO;
use App\DTO\NotificationUpdateDTO;
use App\Models\Notification;
use Illuminate\Database\Eloquent\Collection;

interface INotificationRepository
{
    public function create(NotificationCreateDTO $dto): Notification;
    public function update(NotificationUpdateDTO $dto): Notification;
    public function getByUserId(string $recipientId): Collection;
}
