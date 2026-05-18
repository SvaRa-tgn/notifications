<?php

namespace App\Interface;

interface INotificationDispatcher
{
    public function dispatch(array $data, string $priority, string $key): bool;
}
