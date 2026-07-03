<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UrlController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web-маршруты приложения Linker
|--------------------------------------------------------------------------
*/

// Редирект на страницу входа
Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

// Создание короткой ссылки (AJAX и обычный POST)
Route::post('/shorten', [UrlController::class, 'shorten'])->name('shorten');

// API-маршруты с префиксом /api и middleware auth
Route::middleware('auth')->prefix('api')->group(function () {
    Route::get('/user/urls', [UrlController::class, 'getUserUrls'])->name('api.user.urls');
    Route::get('/url/{id}/stats', [UrlController::class, 'getUrlStats'])->name('api.url.stats');
    Route::delete('/url/{id}', [UrlController::class, 'deleteUrl'])->name('api.url.delete');
});

Route::get('/dashboard', function () {
    return redirect()->route('home');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Редирект по короткому коду — маршрут должен быть последним
Route::get('/{shortCode}', [UrlController::class, 'redirect'])
    ->where('shortCode', '[A-Za-z0-9]{6}')
    ->name('redirect');
