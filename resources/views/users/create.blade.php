<x-layouts::app :title="'Nuevo Usuario'">
    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h1 class="text-2xl font-semibold text-slate-950 dark:text-white">Nuevo usuario</h1>
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-300">Crea un usuario y define en qué empresas participa y con qué rol opera.</p>

        <form method="POST" action="{{ route('users.store') }}" class="mt-6">
            @include('users.form')
        </form>
    </div>
</x-layouts::app>
