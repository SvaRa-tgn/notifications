<?php

namespace App\Services\Sms;

use App\Interface\INotificationMethod;
use Illuminate\Support\Facades\Log;

class SmsProviderService implements INotificationMethod
{
    /**
     * Отправить SMS (заглушка).
     * @param string $recipientId
     * @param string $message
     * @return array
     */
    public function send(string $recipientId, string $message): array
    {
        Log::info(
            message: 'Отправляем сообщение по СМС',
            context: [
                'recipient_id' => $recipientId,
                'message'      => $message,
            ],
        );

        // Имитация успешной отправки
        return [
            'success'             => true,
            'provider_message_id' => 'sms_' . uniqid(),
        ];

        // Имитация ошибки:
        // throw new \Exception('Invalid phone number');
    }
}

