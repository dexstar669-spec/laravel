# Linker — Сервис сокращения ссылок

Веб-приложение на **Laravel 10**, **FilamentPHP v3**, **Laravel Breeze**, **Tailwind CSS** и **jQuery** для сокращения URL с отслеживанием статистики переходов.

## Возможности

- Сокращение ссылок (6-символьный код: буквы + цифры)
- Регистрация, вход и выход (Laravel Breeze)
- AJAX-форма на главной странице (jQuery)
- Личный кабинет `/admin` (FilamentPHP v3)
- Статистика переходов: IP, User Agent, дата/время
- Защита CSRF, валидация URL, Eloquent ORM

## Требования

- PHP 8.1+
- Composer
- Node.js 18+ и npm
- MySQL / MariaDB
- Apache с mod_rewrite (или `php artisan serve`)

## Установка

### 1. Клонирование репозитория

```bash
git clone <url-репозитория> linker
cd linker
```

### 2. Установка зависимостей

```bash
composer install
npm install
npm run build
```

> На Windows с XAMPP используйте: `C:\xampp\php\php.exe composer.phar install`

### 3. Настройка `.env`

```bash
cp .env.example .env
php artisan key:generate
```

Отредактируйте `.env`:

```env
APP_NAME=Linker
APP_URL=http://linker.local

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=linker
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Создание базы данных

В phpMyAdmin или MySQL CLI:

```sql
CREATE DATABASE linker CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 5. Миграции

```bash
php artisan migrate
```

### 6. Создание пользователя Filament

Зарегистрируйтесь через `/register` или создайте пользователя:

```bash
php artisan make:filament-user
```

### 7. Запуск сервера разработки

```bash
php artisan serve
```

Откройте: http://127.0.0.1:8000

### 8. Настройка Apache (опционально)

1. Добавьте в `C:\Windows\System32\drivers\etc\hosts`:
   ```
   127.0.0.1 linker.local
   ```

2. Подключите виртуальный хост из `apache/linker-vhost.conf` в `httpd-vhosts.conf`

3. Перезапустите Apache

4. Откройте: http://linker.local

## Маршруты

| Метод  | URL                    | Описание                    |
|--------|------------------------|-----------------------------|
| GET    | `/`                    | Главная страница            |
| POST   | `/shorten`             | Создание короткой ссылки    |
| GET    | `/{shortCode}`         | Редирект (6 символов)       |
| GET    | `/admin`               | Личный кабинет (Filament)   |
| GET    | `/api/user/urls`       | Список ссылок (auth)        |
| GET    | `/api/url/{id}/stats`  | Статистика (auth)           |
| DELETE | `/api/url/{id}`        | Удаление (auth)             |

## Структура проекта

```
app/
├── Filament/Resources/ShortUrlResource.php
├── Http/Controllers/UrlController.php
├── Models/ShortUrl.php, Click.php, User.php
├── Providers/Filament/AdminPanelProvider.php
└── Services/UrlShortener.php
```

## Лицензия

MIT
