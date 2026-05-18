<?php

namespace App\Actions;

use App\Http\Resources\NotificationResource;
use App\Interface\INotificationRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class GetAction
{
    public function __construct(
        private readonly INotificationRepository $notificationRepository,
    ){}

    /**
     * @param string $recipientId
     * @return JsonResponse
     */
    public function execute(string $recipientId): JsonResponse
    {
        return new JsonResponse(
            data: new NotificationResource($this->notificationRepository->getByUserId($recipientId)),
            status: Response::HTTP_OK,
        );
    }
}
