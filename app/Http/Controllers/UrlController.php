<?php

namespace App\Http\Controllers;

use App\Models\ShortUrl;
use App\Services\UrlShortener;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class UrlController extends Controller
{
    public function __construct(
        private readonly UrlShortener $urlShortener
    ) {
    }

    /**
     * Создание короткой ссылки (POST /shorten).
     */
    public function shorten(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'original_url' => ['required', 'url', 'max:2048'],
        ]);

        $shortUrl = $this->urlShortener->createShortUrl(
            $validated['original_url'],
            Auth::id()
        );

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $shortUrl->id,
                'original_url' => $shortUrl->original_url,
                'short_code' => $shortUrl->short_code,
                'short_url' => $shortUrl->short_url,
                'clicks' => $shortUrl->clicks,
                'created_at' => $shortUrl->created_at?->toDateTimeString(),
            ],
        ], Response::HTTP_CREATED);
    }

    /**
     * Редирект на оригинальный URL с сохранением статистики (GET /{shortCode}).
     */
    public function redirect(string $shortCode, Request $request): RedirectResponse
    {
        $shortUrl = ShortUrl::where('short_code', $shortCode)->firstOrFail();

        // Сохраняем информацию о переходе
        $shortUrl->clickRecords()->create([
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Увеличиваем счётчик кликов
        $shortUrl->increment('clicks');

        return redirect()->away($shortUrl->original_url);
    }

    /**
     * Получение списка ссылок текущего пользователя (GET /api/user/urls).
     */
    public function getUserUrls(): JsonResponse
    {
        $urls = ShortUrl::query()
            ->where('user_id', Auth::id())
            ->latest()
            ->get()
            ->map(fn (ShortUrl $url) => [
                'id' => $url->id,
                'original_url' => $url->original_url,
                'short_code' => $url->short_code,
                'short_url' => $url->short_url,
                'clicks' => $url->clicks,
                'created_at' => $url->created_at?->toDateTimeString(),
            ]);

        return response()->json([
            'success' => true,
            'data' => $urls,
        ]);
    }

    /**
     * Получение статистики по ссылке (GET /api/url/{id}/stats).
     */
    public function getUrlStats(int $id): JsonResponse
    {
        $shortUrl = ShortUrl::query()
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->with(['clickRecords' => fn ($query) => $query->latest()])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $shortUrl->id,
                'original_url' => $shortUrl->original_url,
                'short_code' => $shortUrl->short_code,
                'short_url' => $shortUrl->short_url,
                'clicks' => $shortUrl->clicks,
                'click_records' => $shortUrl->clickRecords->map(fn ($click) => [
                    'ip_address' => $click->ip_address,
                    'user_agent' => $click->user_agent,
                    'created_at' => $click->created_at?->toDateTimeString(),
                ]),
            ],
        ]);
    }

    /**
     * Удаление ссылки пользователя (DELETE /api/url/{id}).
     */
    public function deleteUrl(int $id): JsonResponse
    {
        $shortUrl = ShortUrl::query()
            ->where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $shortUrl->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ссылка успешно удалена.',
        ]);
    }
}
