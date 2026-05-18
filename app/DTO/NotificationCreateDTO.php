<?php

namespace App\DTO;

use App\Enums\NotificationStatus;

readonly class NotificationCreateDTO
{
    public function __construct(
        public NotificationStatus $status,
        public string $idempotencyKey,
        public string $recipientId,
        public string $channel,
        public string $priority,
        public string $message,
    ) {}
}
