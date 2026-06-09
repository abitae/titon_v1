@props(['show' => false, 'maxWidth' => 'max-w-4xl'])

@if ($show)
    <div class="platform-modal-backdrop fixed inset-0 flex items-start justify-center overflow-y-auto bg-slate-950/60 px-4 py-8 backdrop-blur-sm">
        <div class="relative w-full {{ $maxWidth }} rounded-3xl border border-slate-200 bg-white p-6 shadow-2xl dark:border-slate-800 dark:bg-slate-900">
            {{ $slot }}
        </div>

        @isset($stacked)
            {{ $stacked }}
        @endisset
    </div>
@endif
