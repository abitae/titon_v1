<x-layouts::app :title="'Nueva Empresa'">
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h1 class="text-2xl font-semibold text-slate-950 dark:text-white">Nueva empresa</h1>
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Registra una nueva empresa para habilitar su operación en la plataforma.</p>

        <form method="POST" action="{{ route('companies.store') }}" class="mt-6">
            @include('companies.form')
        </form>
    </div>
</x-layouts::app>
