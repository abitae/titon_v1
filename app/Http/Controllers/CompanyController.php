<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveCompanyRequest;
use App\Models\Company;
use App\Services\Ui\Toast;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class CompanyController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:companies.ver', only: ['index']),
            new Middleware('permission:companies.crear', only: ['create', 'store']),
            new Middleware('permission:companies.editar', only: ['edit', 'update']),
            new Middleware('permission:companies.eliminar', only: ['destroy']),
        ];
    }

    public function index(): View
    {
        $this->authorize('viewAny', Company::class);

        return view('companies.index', [
            'companies' => Company::query()->orderBy('name')->paginate(10),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Company::class);

        return view('companies.create');
    }

    public function store(SaveCompanyRequest $request): RedirectResponse
    {
        $this->authorize('create', Company::class);

        Company::query()->create($request->validated());

        Toast::flashSuccess('Empresa creada correctamente.');

        return redirect()->route('companies.index');
    }

    public function edit(Company $company): View
    {
        $this->authorize('update', $company);

        return view('companies.edit', [
            'company' => $company,
        ]);
    }

    public function update(SaveCompanyRequest $request, Company $company): RedirectResponse
    {
        $this->authorize('update', $company);

        $company->update($request->validated());

        Toast::flashSuccess('Empresa actualizada correctamente.');

        return redirect()->route('companies.index');
    }

    public function destroy(Company $company): RedirectResponse
    {
        $this->authorize('delete', $company);

        $company->delete();

        Toast::flashWarning('Empresa eliminada correctamente.');

        return redirect()->route('companies.index');
    }
}
