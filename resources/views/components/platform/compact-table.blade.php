@props(['headers' => [], 'dense' => false])

@php
    $headerCellClass = $dense
        ? 'px-2.5 py-1.5 text-[10px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400'
        : 'px-6 py-4 text-xs uppercase tracking-[0.18em] text-slate-500 dark:text-slate-400';

    $wrapperClass = $dense
        ? 'overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900'
        : 'overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900';
@endphp

<div {{ $attributes->class($wrapperClass) }}>
    <div class="overflow-x-auto">
        <table @class([
            'min-w-full divide-y divide-slate-200 dark:divide-slate-800',
            'text-xs' => $dense,
        ])>
            @if ($headers !== [])
                <thead class="bg-slate-50 dark:bg-slate-950/60">
                    <tr class="text-left">
                        @foreach ($headers as $header)
                            <th @class([$headerCellClass, 'text-right' => $header === '' || str($header)->endsWith('.')])>{{ $header }}</th>
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
