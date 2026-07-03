<?php

namespace App\Filament\Resources\ShortUrlResource\Pages;

use App\Filament\Resources\ShortUrlResource;
use App\Services\UrlShortener;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateShortUrl extends CreateRecord
{
    protected static string $resource = ShortUrlResource::class;

    protected static ?string $title = 'Создание короткой ссылки';

    /**
     * Создание записи через сервис UrlShortener.
     */
    protected function handleRecordCreation(array $data): Model
    {
        return app(UrlShortener::class)->createShortUrl(
            $data['original_url'],
            auth()->id()
        );
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
