<?php

namespace App\Services\Email;

use App\Interface\INotificationMethod;
use Illuminate\Support\Facades\Log;

class EmailProviderService implements INotificationMethod
{
    /**
     * Отправить Email (заглушка).
     *
     * @param string $recipientId
     * @param string $message
     * @return array
     */
    public function send(string $recipientId, string $message): array
    {
        Log::info(
            message: 'Отправляем сообщение по email',
            context: [
                'recipient_id' => $recipientId,
                'message'      => $message,
            ],
        );

        // Имитация успешной отправки
        return [
            'success'             => true,
            'provider_message_id' => 'email_' . uniqid(),
        ];
    }
}
