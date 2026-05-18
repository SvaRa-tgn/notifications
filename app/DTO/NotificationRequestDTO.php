<?php

namespace App\DTO;

readonly class NotificationRequestDTO
{
    public function __construct(
        public string $channel,
        public string $message,
        public string $priority,
        public array  $recipients,
    ) {}
}
