<?php

use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('notifications')->group(function () {
    Route::post('/send', [NotificationController::class, 'send']);
    Route::get('/{recipientId}', [NotificationController::class, 'show']);
});

