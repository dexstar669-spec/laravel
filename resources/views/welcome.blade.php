<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Linker') }} — Сокращение ссылок</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 font-sans text-white antialiased">
    {{-- Шапка --}}
    <header class="border-b border-white/10 bg-black/20 backdrop-blur">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4">
            <a href="{{ route('home') }}" class="text-xl font-bold tracking-tight">
                <span class="text-indigo-400">Linker</span>
            </a>
            <nav class="flex items-center gap-3 text-sm">
                @auth
                    <a href="{{ url('/admin') }}" class="rounded-lg px-3 py-2 text-gray-300 transition hover:bg-white/10 hover:text-white">
                        Личный кабинет
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="rounded-lg px-3 py-2 text-gray-300 transition hover:bg-white/10 hover:text-white">
                            Выйти
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="rounded-lg px-3 py-2 text-gray-300 transition hover:bg-white/10 hover:text-white">
                        Вход
                    </a>
                    <a href="{{ route('register') }}" class="rounded-lg bg-indigo-500 px-4 py-2 font-medium text-white transition hover:bg-indigo-400">
                        Регистрация
                    </a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-6xl px-4 py-12">
        {{-- Герой-блок --}}
        <section class="mb-12 text-center">
            <h1 class="mb-4 text-4xl font-bold tracking-tight md:text-5xl">
                Сокращайте ссылки <span class="text-indigo-400">мгновенно</span>
            </h1>
            <p class="mx-auto max-w-2xl text-lg text-gray-300">
                Бесплатный сервис для создания коротких ссылок с отслеживанием статистики переходов.
            </p>
        </section>

        {{-- Форма сокращения --}}
        <section class="mx-auto mb-10 max-w-3xl">
            <div class="rounded-2xl border border-white/10 bg-white/5 p-6 shadow-2xl backdrop-blur md:p-8">
                <form id="shorten-form" class="space-y-4">
                    @csrf
                    <label for="original_url" class="block text-sm font-medium text-gray-200">
                        Введите URL для сокращения
                    </label>
                    <div class="flex flex-col gap-3 sm:flex-row">
                        <input
                            type="url"
                            id="original_url"
                            name="original_url"
                            required
                            placeholder="https://example.com/very/long/url"
                            class="w-full rounded-xl border border-white/10 bg-white/10 px-4 py-3 text-white placeholder-gray-400 outline-none transition focus:border-indigo-400 focus:ring-2 focus:ring-indigo-400/30"
                        >
                        <button
                            type="submit"
                            id="shorten-btn"
                            class="inline-flex items-center justify-center rounded-xl bg-indigo-500 px-6 py-3 font-semibold text-white transition hover:bg-indigo-400 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            Сократить
                        </button>
                    </div>
                    <p id="form-error" class="hidden text-sm text-red-400"></p>
                </form>

                {{-- Результат --}}
                <div id="result-block" class="mt-6 hidden rounded-xl border border-emerald-500/30 bg-emerald-500/10 p-5">
                    <p class="mb-2 text-sm font-medium text-emerald-300">Ваша короткая ссылка готова!</p>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <input
                            type="text"
                            id="short-url-output"
                            readonly
                            class="w-full rounded-lg border border-white/10 bg-black/30 px-4 py-2 font-mono text-sm text-emerald-200"
                        >
                        <button
                            type="button"
                            id="copy-btn"
                            class="whitespace-nowrap rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-500"
                        >
                            Копировать
                        </button>
                    </div>
                    <p class="mt-3 text-sm text-gray-300">
                        Переходов: <span id="clicks-count" class="font-semibold text-white">0</span>
                    </p>
                </div>
            </div>
        </section>

        {{-- Список ссылок авторизованного пользователя --}}
        @auth
            <section class="rounded-2xl border border-white/10 bg-white/5 p-6 backdrop-blur md:p-8">
                <div class="mb-6 flex items-center justify-between">
                    <h2 class="text-2xl font-bold">Мои ссылки</h2>
                    <button id="refresh-urls" type="button" class="text-sm text-indigo-300 hover:text-indigo-200">
                        Обновить
                    </button>
                </div>

                <div id="urls-loading" class="py-8 text-center text-gray-400">Загрузка...</div>
                <div id="urls-empty" class="hidden py-8 text-center text-gray-400">
                    У вас пока нет сохранённых ссылок. Создайте первую!
                </div>

                <div id="urls-table-wrapper" class="hidden overflow-x-auto">
                    <table class="min-w-full divide-y divide-white/10">
                        <thead>
                            <tr class="text-left text-xs uppercase tracking-wider text-gray-400">
                                <th class="px-4 py-3">Оригинал</th>
                                <th class="px-4 py-3">Короткая</th>
                                <th class="px-4 py-3">Клики</th>
                                <th class="px-4 py-3">Дата</th>
                                <th class="px-4 py-3 text-right">Действия</th>
                            </tr>
                        </thead>
                        <tbody id="urls-list" class="divide-y divide-white/10"></tbody>
                    </table>
                </div>
            </section>

            {{-- Модальное окно статистики --}}
            <div id="stats-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 p-4 backdrop-blur-sm">
                <div class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-2xl border border-white/10 bg-slate-900 p-6 shadow-2xl">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="text-xl font-bold">Статистика переходов</h3>
                        <button type="button" id="close-stats" class="rounded-lg p-2 hover:bg-white/10">&times;</button>
                    </div>
                    <div id="stats-content" class="space-y-4 text-sm text-gray-300"></div>
                </div>
            </div>
        @else
            <section class="rounded-2xl border border-indigo-500/20 bg-indigo-500/10 p-6 text-center">
                <p class="text-gray-200">
                    <a href="{{ route('register') }}" class="font-semibold text-indigo-300 hover:underline">Зарегистрируйтесь</a>,
                    чтобы сохранять ссылки и просматривать статистику в личном кабинете.
                </p>
            </section>
        @endauth
    </main>

    <footer class="border-t border-white/10 py-6 text-center text-sm text-gray-500">
        &copy; {{ date('Y') }} {{ config('app.name', 'Linker') }}. Laravel 10 + FilamentPHP v3
    </footer>

    <script>
        $(function () {
            // Настройка CSRF-токена для всех AJAX-запросов jQuery
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Отправка формы сокращения без перезагрузки страницы
            $('#shorten-form').on('submit', function (e) {
                e.preventDefault();

                const $btn = $('#shorten-btn');
                const $error = $('#form-error');

                $error.addClass('hidden').text('');
                $btn.prop('disabled', true).text('Обработка...');

                $.ajax({
                    url: '{{ route('shorten') }}',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function (response) {
                        const data = response.data;
                        $('#short-url-output').val(data.short_url);
                        $('#clicks-count').text(data.clicks);
                        $('#result-block').removeClass('hidden');

                        @auth
                        loadUserUrls();
                        @endauth
                    },
                    error: function (xhr) {
                        let message = 'Произошла ошибка при создании ссылки.';
                        if (xhr.responseJSON && xhr.responseJSON.errors) {
                            message = Object.values(xhr.responseJSON.errors).flat().join(' ');
                        } else if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        $error.removeClass('hidden').text(message);
                    },
                    complete: function () {
                        $btn.prop('disabled', false).text('Сократить');
                    }
                });
            });

            // Копирование короткой ссылки в буфер обмена
            $('#copy-btn').on('click', function () {
                const url = $('#short-url-output').val();
                navigator.clipboard.writeText(url).then(function () {
                    const $btn = $('#copy-btn');
                    const original = $btn.text();
                    $btn.text('Скопировано!');
                    setTimeout(function () { $btn.text(original); }, 2000);
                });
            });

            @auth
            // Загрузка списка ссылок пользователя
            function loadUserUrls() {
                $('#urls-loading').removeClass('hidden');
                $('#urls-empty').addClass('hidden');
                $('#urls-table-wrapper').addClass('hidden');

                $.get('{{ route('api.user.urls') }}', function (response) {
                    const urls = response.data || [];
                    const $list = $('#urls-list');
                    $list.empty();

                    if (urls.length === 0) {
                        $('#urls-empty').removeClass('hidden');
                    } else {
                        urls.forEach(function (url) {
                            $list.append(`
                                <tr class="hover:bg-white/5">
                                    <td class="max-w-xs truncate px-4 py-3" title="${escapeHtml(url.original_url)}">${escapeHtml(url.original_url)}</td>
                                    <td class="px-4 py-3">
                                        <a href="${escapeHtml(url.short_url)}" target="_blank" class="font-mono text-indigo-300 hover:underline">${escapeHtml(url.short_url)}</a>
                                    </td>
                                    <td class="px-4 py-3">${url.clicks}</td>
                                    <td class="px-4 py-3 whitespace-nowrap">${escapeHtml(url.created_at)}</td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">
                                        <button type="button" class="stats-btn mr-2 rounded-lg bg-indigo-600 px-3 py-1 text-xs hover:bg-indigo-500" data-id="${url.id}">Статистика</button>
                                        <button type="button" class="delete-btn rounded-lg bg-red-600 px-3 py-1 text-xs hover:bg-red-500" data-id="${url.id}">Удалить</button>
                                    </td>
                                </tr>
                            `);
                        });
                        $('#urls-table-wrapper').removeClass('hidden');
                    }
                }).always(function () {
                    $('#urls-loading').addClass('hidden');
                });
            }

            // Защита от XSS при выводе данных
            function escapeHtml(text) {
                return $('<div>').text(text ?? '').html();
            }

            $('#refresh-urls').on('click', loadUserUrls);
            loadUserUrls();

            // Просмотр статистики по ссылке
            $(document).on('click', '.stats-btn', function () {
                const id = $(this).data('id');

                $.get('/api/url/' + id + '/stats', function (response) {
                    const data = response.data;
                    let rows = '';

                    if (data.click_records.length === 0) {
                        rows = '<tr><td colspan="3" class="px-4 py-3 text-center text-gray-500">Переходов пока не было</td></tr>';
                    } else {
                        data.click_records.forEach(function (click) {
                            rows += `
                                <tr class="border-t border-white/10">
                                    <td class="px-4 py-2">${escapeHtml(click.ip_address || '—')}</td>
                                    <td class="max-w-xs truncate px-4 py-2" title="${escapeHtml(click.user_agent)}">${escapeHtml(click.user_agent || '—')}</td>
                                    <td class="px-4 py-2 whitespace-nowrap">${escapeHtml(click.created_at)}</td>
                                </tr>
                            `;
                        });
                    }

                    $('#stats-content').html(`
                        <div class="grid gap-4 md:grid-cols-2">
                            <div><span class="text-gray-500">Оригинал:</span><br><a href="${escapeHtml(data.original_url)}" target="_blank" class="break-all text-indigo-300 hover:underline">${escapeHtml(data.original_url)}</a></div>
                            <div><span class="text-gray-500">Короткая:</span><br><span class="font-mono">${escapeHtml(data.short_url)}</span></div>
                        </div>
                        <div class="rounded-lg bg-indigo-500/20 p-4">
                            <span class="text-gray-400">Всего переходов:</span>
                            <span class="ml-2 text-2xl font-bold text-white">${data.clicks}</span>
                        </div>
                        <div class="overflow-x-auto rounded-lg border border-white/10">
                            <table class="min-w-full text-left">
                                <thead class="bg-white/5 text-xs uppercase text-gray-400">
                                    <tr>
                                        <th class="px-4 py-2">IP</th>
                                        <th class="px-4 py-2">User Agent</th>
                                        <th class="px-4 py-2">Дата</th>
                                    </tr>
                                </thead>
                                <tbody>${rows}</tbody>
                            </table>
                        </div>
                    `);

                    $('#stats-modal').removeClass('hidden').addClass('flex');
                });
            });

            $('#close-stats, #stats-modal').on('click', function (e) {
                if (e.target === this) {
                    $('#stats-modal').addClass('hidden').removeClass('flex');
                }
            });

            // Удаление ссылки с подтверждением
            $(document).on('click', '.delete-btn', function () {
                const id = $(this).data('id');

                if (!confirm('Вы уверены, что хотите удалить эту ссылку?')) {
                    return;
                }

                $.ajax({
                    url: '/api/url/' + id,
                    method: 'DELETE',
                    success: function () {
                        loadUserUrls();
                    },
                    error: function () {
                        alert('Не удалось удалить ссылку.');
                    }
                });
            });
            @endauth
        });
    </script>
</body>
</html>
