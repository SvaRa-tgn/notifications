<?php

namespace App\Http\Controllers;

use App\Actions\GetAction;
use App\Actions\SendAction;
use App\Http\Requests\SendNotificationRequest;
use Symfony\Component\HttpFoundation\JsonResponse;

class NotificationController extends Controller
{
    /**
     * Запуск массовой рассылки уведомлений.
     *
     * @param SendNotificationRequest $request
     * @param SendAction $action
     * @return JsonResponse
     */
    public function send(SendNotificationRequest $request, SendAction $action): JsonResponse
    {
        return $action->execute($request->getDTO());
    }

    /**
     * Получить статусы уведомлений подписчика.
     *
     * @param string $recipientId
     * @param GetAction $action
     * @return JsonResponse
     */
    public function show(string $recipientId, GetAction $action): JsonResponse
    {
        return $action->execute($recipientId);
    }
}
