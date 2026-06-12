@props(['headers' => []])

<div {{ $attributes->class('overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900') }}>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-xs dark:divide-slate-800">
            @if ($headers !== [])
                <thead class="bg-slate-50 dark:bg-slate-950/60">
                    <tr class="text-left">
                        @foreach ($headers as $header)
                            <th @class([
                                'px-2.5 py-1.5 text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400',
                                'text-right' => $header === '' || str($header)->endsWith('.'),
                            ])>{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
            @endif

            <tbody class="divide-y divide-slate-200 dark:divide-slate-800 [&_td]:px-2.5 [&_td]:py-1.5 [&_td]:align-middle [&_td]:text-xs [&_td]:text-slate-700 dark:[&_td]:text-slate-200">
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>
