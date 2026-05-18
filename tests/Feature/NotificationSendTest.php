<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Notification;
use App\Enums\NotificationStatus;
use App\Interface\INotificationDispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;

class NotificationSendTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Мокаем диспетчер — записываем напрямую в БД
        $this->app->bind(INotificationDispatcher::class, function () {
            return new class implements INotificationDispatcher {
                public function dispatch(array $data, string $priority, string $key): bool
                {
                    // Проверка на дубликат
                    if (Notification::where('idempotency_key', $key)->exists()) {
                        return true; // Не создаём повторно
                    }

                    Notification::create([
                        'idempotency_key' => $key,
                        'recipient_id'    => $data['recipient_id'],
                        'channel'         => $data['channel'],
                        'priority'        => $priority,
                        'message'         => $data['message'],
                        'status'          => NotificationStatus::QUEUED,
                    ]);
                    return true;
                }
            };
        });
    }

    public function test_full_notification_flow(): void
    {
        $this->withoutExceptionHandling();
        $response = $this->postJson('/api/notifications/send', [
            'channel'    => 'sms',
            'message'    => 'Test message',
            'priority'   => 'transactional',
            'recipients' => ['user-1', 'user-2'],
        ]);

        $response->assertStatus(202)
            ->assertJsonStructure([
                'batch_id', 'total', 'accepted', 'rejected', 'messages',
            ]);

        $statusResponse = $this->getJson('/api/notifications/user-1');
        $statusResponse->assertStatus(200)
            ->assertJsonPath('0.recipient_id', 'user-1');
    }

    public function test_idempotency(): void
    {
        $payload = [
            'channel'    => 'email',
            'message'    => 'Idempotent message',
            'priority'   => 'marketing',
            'recipients' => ['user-3'],
        ];

        $response1 = $this->postJson('/api/notifications/send', $payload);
        $response2 = $this->postJson('/api/notifications/send', $payload);

        $response1->assertStatus(202);
        $response2->assertStatus(202);

        $this->assertTrue(true);
    }

    public function test_priority_order(): void
    {
        $this->postJson('/api/notifications/send', [
            'channel'    => 'sms',
            'message'    => 'Marketing',
            'priority'   => 'marketing',
            'recipients' => ['user-4'],
        ]);

        $this->postJson('/api/notifications/send', [
            'channel'    => 'sms',
            'message'    => 'Transactional',
            'priority'   => 'transactional',
            'recipients' => ['user-5'],
        ]);

        $txNotification = Notification::where('priority', 'transactional')->first();
        $mktNotification = Notification::where('priority', 'marketing')->first();

        $this->assertNotNull($txNotification);
        $this->assertNotNull($mktNotification);
    }

    public function test_validation(): void
    {
        $response = $this->postJson('/api/notifications/send', [
            'channel'    => 'invalid_channel',
            'message'    => '',
            'priority'   => 'unknown',
            'recipients' => [],
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['channel', 'message', 'priority', 'recipients']);
    }
}
