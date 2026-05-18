<?php

namespace App\Enums;

enum NotificationPriority: string
{
    case TRANSACTIONAL = 'transactional';
    case INFORMATIONAL = 'informational';
    case MARKETING     = 'marketing';

    public function topic(): string
    {
        return match($this) {
            self::TRANSACTIONAL => 'notifications-tx',
            self::INFORMATIONAL => 'notifications-info',
            self::MARKETING     => 'notifications-marketing',
        };
    }

    public function weight(): int
    {
        return match($this) {
            self::TRANSACTIONAL => 1,
            self::INFORMATIONAL => 2,
            self::MARKETING     => 3,
        };
    }
}
