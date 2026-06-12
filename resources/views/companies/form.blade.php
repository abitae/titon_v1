@csrf

<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Nombre comercial</label>
        <input id="name" name="name" value="{{ old('name', $company->name ?? '') }}" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
        @error('name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="correlative_prefix" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Prefijo correlativos</label>
        <input id="correlative_prefix" name="correlative_prefix" value="{{ old('correlative_prefix', $company->correlative_prefix ?? '') }}" maxlength="32" placeholder="Ej: TITON" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
        @error('correlative_prefix') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
        <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Letras, numeros, guion y guion bajo. Si se deja vacio se deriva del nombre comercial.</p>
    </div>
    <div>
        <label for="business_name" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Razón social</label>
        <input id="business_name" name="business_name" value="{{ old('business_name', $company->business_name ?? '') }}" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
        @error('business_name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="ruc" class="block text-sm font-medium text-slate-700 dark:text-slate-200">RUC</label>
        <input id="ruc" name="ruc" value="{{ old('ruc', $company->ruc ?? '') }}" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
        @error('ruc') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Correo</label>
        <input id="email" name="email" type="email" value="{{ old('email', $company->email ?? '') }}" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
        @error('email') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="phone" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Teléfono</label>
        <input id="phone" name="phone" value="{{ old('phone', $company->phone ?? '') }}" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
        @error('phone') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="status" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Estado</label>
        <select id="status" name="status" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
            <option value="active" @selected(old('status', $company->status ?? 'active') === 'active')>active</option>
            <option value="inactive" @selected(old('status', $company->status ?? 'active') === 'inactive')>inactive</option>
        </select>
        @error('status') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>
    <div class="md:col-span-2">
        <label for="address" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Dirección</label>
        <input id="address" name="address" value="{{ old('address', $company->address ?? '') }}" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
        @error('address') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="primary_color" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Color primario</label>
        <input id="primary_color" name="primary_color" value="{{ old('primary_color', $company->primary_color ?? '#0f172a') }}" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
        @error('primary_color') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="secondary_color" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Color secundario</label>
        <input id="secondary_color" name="secondary_color" value="{{ old('secondary_color', $company->secondary_color ?? '#0891b2') }}" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-2 py-1 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
        @error('secondary_color') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>
</div>

<div class="mt-6 flex items-center justify-end gap-3">
    <a href="{{ route('companies.index') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 dark:border-slate-700 dark:text-slate-200">Cancelar</a>
    <button type="submit" class="rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 dark:bg-cyan-500 dark:text-slate-950 dark:hover:bg-cyan-400">
        Guardar empresa
    </button>
</div>
