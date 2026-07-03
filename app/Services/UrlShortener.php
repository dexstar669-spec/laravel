<?php

namespace App\Services;

use App\Models\ShortUrl;

class UrlShortener
{
    /**
     * Длина генерируемого короткого кода.
     */
    private const CODE_LENGTH = 6;

    /**
     * Максимальное количество попыток генерации уникального кода.
     */
    private const MAX_ATTEMPTS = 10;

    /**
     * Генерация уникального короткого кода (буквы и цифры).
     */
    public function generateShortCode(): string
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $attempts = 0;

        do {
            $code = '';

            for ($i = 0; $i < self::CODE_LENGTH; $i++) {
                $code .= $characters[random_int(0, strlen($characters) - 1)];
            }

            $attempts++;
        } while (
            ShortUrl::where('short_code', $code)->exists()
            && $attempts < self::MAX_ATTEMPTS
        );

        if (ShortUrl::where('short_code', $code)->exists()) {
            throw new \RuntimeException('Не удалось сгенерировать уникальный короткий код.');
        }

        return $code;
    }

    /**
     * Создание короткой ссылки для указанного URL.
     */
    public function createShortUrl(string $originalUrl, ?int $userId = null): ShortUrl
    {
        return ShortUrl::create([
            'user_id' => $userId,
            'original_url' => $originalUrl,
            'short_code' => $this->generateShortCode(),
            'clicks' => 0,
        ]);
    }
}
