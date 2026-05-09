<?php

namespace App\Http\Controllers;

use App\Actions\Users\SyncUserCompanies;
use App\Http\Requests\SaveUserRequest;
use App\Models\Company;
use App\Models\User;
use App\Services\Ui\Toast;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:users.ver', only: ['index']),
            new Middleware('permission:users.crear', only: ['create', 'store']),
            new Middleware('permission:users.editar', only: ['edit', 'update']),
            new Middleware('permission:users.eliminar', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        return view('users.index', [
            'users' => User::query()
                ->with(['companies' => fn ($query) => $query->orderBy('companies.name')])
                ->orderBy('name')
                ->paginate(10),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('users.create', $this->formData());
    }

    public function store(SaveUserRequest $request, SyncUserCompanies $syncUserCompanies): RedirectResponse
    {
        $this->authorize('create', User::class);

        $user = User::query()->create([
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'password' => Hash::make($request->string('password')->toString()),
        ]);

        $syncUserCompanies->handle(
            $user,
            $request->array('company_ids'),
            $request->array('role_ids'),
            $request->array('active_company_ids'),
            $request->filled('default_company_id') ? $request->integer('default_company_id') : null,
        );

        Toast::flashSuccess('Usuario creado correctamente.');

        return redirect()->route('users.index');
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        $user->load('companies');

        return view('users.edit', [
            ...$this->formData(),
            'user' => $user,
        ]);
    }

    public function update(SaveUserRequest $request, User $user, SyncUserCompanies $syncUserCompanies): RedirectResponse
    {
        $this->authorize('update', $user);

        $payload = [
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
        ];

        if ($request->filled('password')) {
            $payload['password'] = Hash::make($request->string('password')->toString());
        }

        $user->update($payload);

        $syncUserCompanies->handle(
            $user,
            $request->array('company_ids'),
            $request->array('role_ids'),
            $request->array('active_company_ids'),
            $request->filled('default_company_id') ? $request->integer('default_company_id') : null,
        );

        Toast::flashSuccess('Usuario actualizado correctamente.');

        return redirect()->route('users.index');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $user->delete();

        Toast::flashWarning('Usuario eliminado correctamente.');

        return redirect()->route('users.index');
    }

    /**
     * @return array<string, mixed>
     */
    protected function formData(): array
    {
        return [
            'companies' => Company::query()->orderBy('name')->get(),
            'roles' => Role::query()->whereNull('company_id')->orderBy('name')->get(),
        ];
    }
}
