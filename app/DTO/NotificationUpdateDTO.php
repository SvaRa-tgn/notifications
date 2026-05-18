<?php

namespace App\DTO;

use App\Enums\NotificationStatus;
use App\Models\Notification;

readonly class NotificationUpdateDTO
{
    public function __construct(
        public Notification $notification,
        public ?NotificationStatus $status = null,
        public int $attempts = 0,
        public ?string $providerMessageId = null,
        public ?string $errorMessage = null,
        public ?string $sentAt = null,
        public ?string $deliveredAt = null,
        public ?string $bouncedAt = null,
    ) {}
}
