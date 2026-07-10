@props([
    'id',
    'title' => null,
    'subtitle' => null,
    'config' => ['type' => 'bar', 'data' => ['labels' => [], 'datasets' => []]],
    'height' => '300',
])

@php
    $configHash = md5(json_encode($config, JSON_THROW_ON_ERROR));
@endphp

<div class="space-y-4" wire:key="chart-panel-{{ $id }}-{{ $configHash }}">
    @if ($title || $subtitle)
        <div class="flex items-start justify-between gap-3">
            <div>
                @if ($title)
                    <h3 class="text-lg font-semibold text-slate-950 dark:text-white">{{ $title }}</h3>
                @endif

                @if ($subtitle)
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $subtitle }}</p>
                @endif
            </div>
        </div>
    @endif

    <div
        data-chart-root
        data-chart-id="{{ $id }}"
        data-chart-config='@json($config)'
        style="height: {{ is_numeric($height) ? $height.'px' : $height }}"
        class="relative min-h-[240px] rounded-2xl bg-slate-50/70 p-3 dark:bg-slate-950/40"
        x-init="$nextTick(() => window.scheduleChartsInit?.())"
    >
        <canvas class="h-full w-full"></canvas>
    </div>
</div>
