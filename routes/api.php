<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Основные API-маршруты приложения расположены в routes/web.php
| с middleware auth для поддержки сессий и CSRF-защиты (jQuery AJAX).
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
