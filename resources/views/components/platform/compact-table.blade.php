@props(['headers' => []])

<div {{ $attributes->class('overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900') }}>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
            @if ($headers !== [])
                <thead class="bg-slate-50 dark:bg-slate-950/60">
                    <tr class="text-left text-xs uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400">
                        @foreach ($headers as $header)
                            <th class="px-6 py-4">{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
            @endif

            <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>
