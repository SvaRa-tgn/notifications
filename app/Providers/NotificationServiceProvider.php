<?php

namespace App\Providers;

use App\Enums\NotificationChannel;
use App\Interface\INotificationDispatcher;
use App\Interface\INotificationRepository;
use App\Repository\NotificationRepository;
use App\Services\Email\EmailProviderService;
use App\Services\Kafka\KafkaDispatcher;
use App\Services\Sms\SmsProviderService;
use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(INotificationDispatcher::class, KafkaDispatcher::class);

        $this->app->bind(INotificationRepository::class, NotificationRepository::class);

        $this->app->tag([
            SmsProviderService::class,
            EmailProviderService::class,
        ], 'notification.methods');

        $this->app->bind('notification.providers', function ($app) {
            return [
                NotificationChannel::SMS->value   => $app->make(SmsProviderService::class),
                NotificationChannel::EMAIL->value => $app->make(EmailProviderService::class),
            ];
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
