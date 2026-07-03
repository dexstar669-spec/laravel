<div class="space-y-6">
    <div class="grid gap-4 md:grid-cols-2">
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Оригинальная ссылка</p>
            <a href="{{ $record->original_url }}" target="_blank" rel="noopener noreferrer"
               class="mt-1 block break-all text-primary-600 hover:underline dark:text-primary-400">
                {{ $record->original_url }}
            </a>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Короткая ссылка</p>
            <p class="mt-1 break-all font-mono text-sm">{{ $record->short_url }}</p>
        </div>
    </div>

    <div class="rounded-lg bg-primary-50 p-4 dark:bg-primary-950">
        <p class="text-sm text-gray-600 dark:text-gray-300">Общее количество переходов</p>
        <p class="text-3xl font-bold text-primary-600 dark:text-primary-400">{{ $record->clicks }}</p>
    </div>

    <div>
        <h3 class="mb-3 text-base font-semibold">История переходов</h3>

        @if ($record->clickRecords->isEmpty())
            <p class="text-sm text-gray-500 dark:text-gray-400">Переходов пока не было.</p>
        @else
            <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">IP-адрес</th>
                            <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">User Agent</th>
                            <th class="px-4 py-2 text-left text-xs font-medium uppercase text-gray-500">Дата и время</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-700 dark:bg-gray-900">
                        @foreach ($record->clickRecords as $click)
                            <tr>
                                <td class="whitespace-nowrap px-4 py-2 text-sm">{{ $click->ip_address ?? '—' }}</td>
                                <td class="max-w-xs truncate px-4 py-2 text-sm" title="{{ $click->user_agent }}">
                                    {{ $click->user_agent ?? '—' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-2 text-sm">
                                    {{ $click->created_at?->format('d.m.Y H:i:s') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
