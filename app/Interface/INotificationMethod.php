<?php

namespace App\Interface;

interface INotificationMethod
{
    public function send(string $recipientId, string $message): array;
}
