@php
    $selectedCompanyIds = collect(old('company_ids', isset($user) ? $user->companies->pluck('id')->all() : []))->map(fn ($id) => (int) $id)->all();
    $activeCompanyIds = collect(old('active_company_ids', isset($user) ? $user->companies->filter(fn ($company) => (bool) $company->pivot->active)->pluck('id')->all() : []))->map(fn ($id) => (int) $id)->all();
    $defaultCompanyId = (int) old('default_company_id', isset($user) ? optional($user->companies->first(fn ($company) => (bool) $company->pivot->default_company))->id : 0);
    $roleIds = old('role_ids', isset($user) ? $user->companies->mapWithKeys(fn ($company) => [$company->id => $company->pivot->role_id])->all() : []);
@endphp

@csrf

<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Nombre</label>
        <input id="name" name="name" value="{{ old('name', $user->name ?? '') }}" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
        @error('name') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Correo</label>
        <input id="email" name="email" type="email" value="{{ old('email', $user->email ?? '') }}" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
        @error('email') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="password" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Contraseña {{ isset($user) ? '(opcional)' : '' }}</label>
        <input id="password" name="password" type="password" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
        @error('password') <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="password_confirmation" class="block text-sm font-medium text-slate-700 dark:text-slate-200">Confirmar contraseña</label>
        <input id="password_confirmation" name="password_confirmation" type="password" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white" />
    </div>
</div>

<div class="mt-8 overflow-hidden rounded-3xl border border-slate-200 dark:border-slate-800">
    <div class="bg-slate-50 px-6 py-4 dark:bg-slate-950/60">
        <h2 class="text-sm font-semibold uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400">Empresas y roles</h2>
    </div>
    <div class="divide-y divide-slate-200 dark:divide-slate-800">
        @foreach ($companies as $company)
            <div class="grid gap-4 px-6 py-5 lg:grid-cols-[1.6fr_1fr_1fr_1fr] lg:items-center">
                <label class="flex items-start gap-3">
                    <input type="checkbox" name="company_ids[]" value="{{ $company->id }}" @checked(in_array($company->id, $selectedCompanyIds, true)) class="mt-1 rounded border-slate-300 text-cyan-600 focus:ring-cyan-500" />
                    <span>
                        <span class="block text-sm font-medium text-slate-950 dark:text-white">{{ $company->name }}</span>
                        <span class="block text-sm text-slate-500 dark:text-slate-400">{{ $company->business_name }}</span>
                    </span>
                </label>

                <div>
                    <label class="block text-xs font-medium uppercase tracking-[0.16em] text-slate-500 dark:text-slate-400">Rol</label>
                    <select name="role_ids[{{ $company->id }}]" class="mt-2 block w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-white">
                        <option value="">Selecciona un rol</option>
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" @selected((int) ($roleIds[$company->id] ?? 0) === $role->id)>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                    @error("role_ids.$company->id") <p class="mt-1 text-sm text-rose-600">{{ $message }}</p> @enderror
                </div>

                <label class="flex items-center gap-3">
                    <input type="checkbox" name="active_company_ids[]" value="{{ $company->id }}" @checked(in_array($company->id, $activeCompanyIds, true)) class="rounded border-slate-300 text-cyan-600 focus:ring-cyan-500" />
                    <span class="text-sm text-slate-700 dark:text-slate-200">Membresía activa</span>
                </label>

                <label class="flex items-center gap-3">
                    <input type="radio" name="default_company_id" value="{{ $company->id }}" @checked($defaultCompanyId === $company->id) class="border-slate-300 text-cyan-600 focus:ring-cyan-500" />
                    <span class="text-sm text-slate-700 dark:text-slate-200">Empresa por defecto</span>
                </label>
            </div>
        @endforeach
    </div>
</div>

@error('company_ids') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
@error('active_company_ids') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror
@error('default_company_id') <p class="mt-2 text-sm text-rose-600">{{ $message }}</p> @enderror

<div class="mt-6 flex items-center justify-end gap-3">
    <a href="{{ route('users.index') }}" class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 dark:border-slate-700 dark:text-slate-200">Cancelar</a>
    <button type="submit" class="rounded-xl bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 dark:bg-cyan-500 dark:text-slate-950 dark:hover:bg-cyan-400">
        Guardar usuario
    </button>
</div>
