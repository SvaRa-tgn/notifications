<?php

namespace App\Actions;

use App\DTO\NotificationRequestDTO;
use App\Interface\INotificationDispatcher;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\JsonResponse;

class SendAction
{
    public function __construct(
        private readonly INotificationDispatcher $dispatcher,
    ) {}

    /**
     * @param NotificationRequestDTO $dto
     * @return JsonResponse
     */
    public function execute(NotificationRequestDTO $dto): JsonResponse
    {
        $batchId = (string)Str::uuid();
        $results = [];

        foreach ($dto->recipients as $recipientId) {
            $idempotencyKey = "{$batchId}:{$recipientId}";
            $success = $this->dispatcher->dispatch(
                data: [
                    'recipient_id' => $recipientId,
                    'channel'      => $dto->channel,
                    'message'      => $dto->message,
                    'batch_id'     => $batchId,
                ],
                priority: $dto->priority,
                key: $idempotencyKey,
            );

            $results[] = [
                'recipient_id'    => $recipientId,
                'channel'         => $dto->channel,
                'message'         => $dto->message,
                'idempotency_key' => $idempotencyKey,
                'status'          => $success ? 'accepted' : 'rejected',
            ];
        }

        $successCount = count(array_filter($results, fn($r) => $r['status'] === 'accepted'));

        return new JsonResponse(
            data: [
                'batch_id' => $batchId,
                'total'    => count($results),
                'accepted' => $successCount,
                'rejected' => count($results) - $successCount,
                'messages' => $results,
            ], status: 202,
        );
    }
}
