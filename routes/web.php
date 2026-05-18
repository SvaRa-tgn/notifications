<?php

use App\Http\Controllers\TestKafkaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/kafka/test', [TestKafkaController::class, 'send']);
Route::get('/kafka/bulk', [TestKafkaController::class, 'bulk']);
