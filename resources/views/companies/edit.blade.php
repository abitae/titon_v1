<x-layouts::app :title="'Editar Empresa'">
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h1 class="text-2xl font-semibold text-slate-950 dark:text-white">Editar empresa</h1>
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Actualiza datos fiscales, contacto y branding de la empresa.</p>

        <form method="POST" action="{{ route('companies.update', $company) }}" class="mt-6">
            @method('PUT')
            @include('companies.form')
        </form>
    </div>
</x-layouts::app>
