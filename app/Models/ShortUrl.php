<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShortUrl extends Model
{
    use HasFactory;

    /**
     * Поля, доступные для массового заполнения.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'original_url',
        'short_code',
        'clicks',
    ];

    /**
     * Приведение типов атрибутов.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'clicks' => 'integer',
    ];

    /**
     * Связь с пользователем-владельцем ссылки.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Связь с записями переходов по ссылке.
     */
    public function clickRecords(): HasMany
    {
        return $this->hasMany(Click::class);
    }

    /**
     * Полный URL короткой ссылки.
     */
    public function getShortUrlAttribute(): string
    {
        return url('/' . $this->short_code);
    }
}
