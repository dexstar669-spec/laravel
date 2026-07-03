<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ShortUrlResource\Pages;
use App\Models\ShortUrl;
use App\Services\UrlShortener;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ShortUrlResource extends Resource
{
    protected static ?string $model = ShortUrl::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';

    protected static ?string $navigationLabel = 'Мои ссылки';

    protected static ?string $modelLabel = 'Короткая ссылка';

    protected static ?string $pluralModelLabel = 'Короткие ссылки';

    /**
     * Фильтрация: пользователь видит только свои ссылки.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', auth()->id());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('original_url')
                    ->label('Оригинальный URL')
                    ->url()
                    ->required()
                    ->maxLength(2048)
                    ->placeholder('https://example.com/page'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('original_url')
                    ->label('Оригинальная ссылка')
                    ->limit(50)
                    ->tooltip(fn (ShortUrl $record): string => $record->original_url)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('short_url')
                    ->label('Короткая ссылка')
                    ->state(fn (ShortUrl $record): string => $record->short_url)
                    ->copyable()
                    ->copyMessage('Ссылка скопирована')
                    ->copyMessageDuration(1500),
                Tables\Columns\TextColumn::make('clicks')
                    ->label('Переходы')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата создания')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('stats')
                    ->label('Статистика')
                    ->icon('heroicon-o-chart-bar')
                    ->slideOver()
                    ->modalHeading('Статистика переходов')
                    ->modalContent(fn (ShortUrl $record) => view(
                        'filament.resources.short-url-resource.partials.stats-modal',
                        ['record' => $record->load(['clickRecords' => fn ($q) => $q->latest()])]
                    )),
                Tables\Actions\DeleteAction::make()
                    ->label('Удалить'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShortUrls::route('/'),
            'create' => Pages\CreateShortUrl::route('/create'),
        ];
    }
}
