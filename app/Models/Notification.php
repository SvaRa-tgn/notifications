<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use App\Enums\NotificationStatus;
use App\Enums\NotificationChannel;
use App\Enums\NotificationPriority;
use Carbon\CarbonImmutable;

/**
 * @property string $id
 * @property string $idempotency_key
 * @property string $recipient_id
 * @property NotificationChannel $channel
 * @property NotificationPriority $priority
 * @property string $message
 * @property NotificationStatus $status
 * @property string|null $error_message
 * @property string|null $provider_message_id
 * @property int $attempts
 * @property CarbonImmutable|null $sent_at
 * @property CarbonImmutable|null $delivered_at
 * @property CarbonImmutable|null $bounced_at
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 */
class Notification extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'idempotency_key',
        'recipient_id',
        'channel',
        'priority',
        'message',
        'status',
        'error_message',
        'provider_message_id',
        'attempts',
        'sent_at',
        'delivered_at',
        'bounced_at',
    ];

    protected $casts = [
        'channel'      => NotificationChannel::class,
        'priority'     => NotificationPriority::class,
        'status'       => NotificationStatus::class,
        'sent_at'      => 'immutable_datetime',
        'delivered_at' => 'immutable_datetime',
        'bounced_at'   => 'immutable_datetime',
        'created_at'   => 'immutable_datetime',
        'updated_at'   => 'immutable_datetime',
    ];

    public function incrementAttempts(): void
    {
        $this->increment('attempts');
    }
}
